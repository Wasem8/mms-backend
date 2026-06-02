<?php

namespace Modules\Community\Services;

use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Community\Models\Sermon;
use Modules\Community\Repositories\SermonRepositoryInterface;

class SermonService
{
    protected $sermonRepo;

    public function __construct(SermonRepositoryInterface $sermonRepo)
    {
        $this->sermonRepo = $sermonRepo;
    }

    public function getAllSermons()
    {
        return Sermon::with('attachments')->get();
    }
    public function getPendingSermons()
    {
        return Sermon::with('attachments')->where('status', 'Pending')->get();
    }

    public function getArchivedSermons()
    {
        return Sermon::with('attachments')->where('status', 'Archived')->get();
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

        return $sermon;
    }

    public function rejectAndDestroySermon(int $sermonId): void
    {
        $sermon = $this->sermonRepo->findById($sermonId);
        $this->sermonRepo->delete($sermon);
    }

    // 4. تنظيف النظام التلقائي من الخطب المتروكة التي حل وقتها ولم تُعتمد
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
        return $this->sermonRepo->updateStatus($sermonId, 'Rejected', $notes, $regionManagerId);
    }


}
