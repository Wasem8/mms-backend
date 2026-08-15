<?php

namespace Modules\Community\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Community\Models\Sermon;
use Modules\Community\Repositories\SermonRepositoryInterface;
use Modules\User\Models\User;
use Modules\Community\Events\SermonApproved;
use Modules\Community\Events\SermonRejected;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;


class SermonService
{
    protected $sermonRepo;

    public function __construct(SermonRepositoryInterface $sermonRepo)
    {
        $this->sermonRepo = $sermonRepo;
    }

    public function getAllSermons(User $user)
    {
        $filters = $this->applyRoleScope([], $user);

        return Sermon::with('attachments')
            ->filter($filters)
            ->latest()
            ->get();
    }
    public function getPendingSermons(int $perPage = 15)
    {
        return Sermon::with('attachments')
            ->where('status', 'Pending')
            ->latest()
            ->paginate($perPage);
    }

    public function getArchivedSermons(int $perPage = 15)
    {
        return Sermon::with('attachments')
            ->where('status', 'Archived')
            ->latest()
            ->paginate($perPage);
    }

    public function createSermon(array $data, int $mosqueManagerId, array $files = []): Sermon
    {
        $data['mosque_manager_id'] = $mosqueManagerId;
        $data['status'] = 'Pending';

        return DB::transaction(function () use ($data, $files) {
            $sermon = $this->sermonRepo->create($data);

            foreach ($files as $file) {
                $fileUrl = $this->uploadImage($file);

                $sermon->attachments()->create([
                    'file_path' => $fileUrl,
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
            return $sermon->load('attachments');
        });
    }

    public function getSermonById(int $sermonId): ?Sermon
    {
        return $this->sermonRepo->findById($sermonId);
    }

    public function approveSermon(int $sermonId, int $adminId): Sermon
    {
        $sermon = $this->sermonRepo->findById($sermonId);

        $sermon->region_manager_id = $adminId;
        $sermon->save();

        $this->sermonRepo->updateStatus($sermon, 'Archived');

        event(new SermonApproved($sermon, $adminId));


        return $sermon;
    }

    public function rejectAndDestroySermon(int $sermonId): void
    {
        $sermon = $this->sermonRepo->findById($sermonId);
        $this->sermonRepo->delete($sermon);
        event(new SermonRejected(
            sermonId: $sermon->id,
            sermonTitle: $sermon->title,
            mosqueManagerId: $sermon->mosque_manager_id,
            isHardReject: true,
        ));
        }

    public function purgeExpiredPendingSermons(): int
    {
        $today = Carbon::today()->toDateString();
        $expiredSermons = $this->sermonRepo->getExpiredPendingSermons($today);

        foreach ($expiredSermons as $sermon) {
            $this->sermonRepo->delete($sermon);
        }

        return count($expiredSermons);
    }
    private function uploadImage($image): string
    {
        $fileName = uniqid() . '.' . $image->getClientOriginalExtension();

        $baseUrl = config('services.supabase.url');
        $bucket = config('services.supabase.bucket');
        $key = config('services.supabase.key');

        $path = $bucket . '/' . $fileName;
        $uploadUrl = $baseUrl . '/storage/v1/object/' . $path;

        $response = Http::withHeaders([
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->attach(
            'file',
            file_get_contents($image),
            $fileName
        )->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception('Upload failed: ' . $response->body());
        }

        return $baseUrl . '/storage/v1/object/public/' . $path;
    }


    public function rejectSermon($sermonId, $regionManagerId, $notes)
    {
        $sermon = $this->sermonRepo->findById($sermonId);
        $updated = $this->sermonRepo->updateStatus($sermonId, 'Rejected', $notes, $regionManagerId);

        if ($sermon) {
            event(new SermonRejected(
                sermonId: $sermon->id,
                sermonTitle: $sermon->title,
                mosqueManagerId: $sermon->mosque_manager_id,
                notes: $notes,
                isHardReject: false,
            ));
        }

        return $updated;
    }

    public function searchSermons(array $filters, User $user, int $perPage = 15)
    {
        $filters = $this->applyRoleScope($filters, $user);

        return $this->sermonRepo->search($filters, $perPage);
    }

    private function applyRoleScope(array $filters, User $user): array
    {
        if ($user->isMosqueManager()) {
            $filters['mosque_manager_id'] = $user->id;
            return $filters;
        }

        if ($user->isAreaManager()) {
            return $filters;
        }

        return $filters;
    }

    public function getMostSelectedSermons(int $limit = 10, ?string $fridayDateFrom = null, ?string $fridayDateTo = null)
    {
        return $this->sermonRepo->mostSelected($limit, $fridayDateFrom, $fridayDateTo);
    }

    public function deleteSermonIfPending(int $sermonId, int $mosqueManagerId): void
    {
        $sermon = $this->sermonRepo->findById($sermonId);

        if (!$sermon) {
            throw new ModelNotFoundException('Sermon not found.');
        }

        if ($sermon->mosque_manager_id !== $mosqueManagerId) {
            throw new AuthorizationException('You are not authorized to delete this sermon.');
        }

        if ($sermon->status !== 'Pending') {
            throw new \RuntimeException('Only pending sermons can be deleted.');
        }

        $this->sermonRepo->delete($sermon);
    }
}
