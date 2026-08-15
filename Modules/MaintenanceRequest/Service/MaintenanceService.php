<?php

namespace Modules\MaintenanceRequest\Service;

use Google\Cloud\Core\Timestamp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\MaintenanceRequest\Repositories\MaintenanceRepositoryInterface;
use Modules\MaintenanceRequest\Events\MaintenanceRequestCreated;
use Modules\MaintenanceRequest\Events\MaintenanceStatusChanged;

class MaintenanceService
{
    public function __construct(
        protected MaintenanceRepositoryInterface $repository,
    ) {}


    public function submitRequest(array $data, array $files = [])
    {

        $manager = auth()->user();
        $data['requested_by']   = $manager->id;
        $data['maintenance_number'] = 'MR-' . date('Y') . '-' . strtoupper(Str::random(6));
        $data['status']             = 'pending';

        $maintenance = $this->repository->create($data);

        if (! empty($files)) {
            $fileRecords = [];

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileRecords[] = [
                        'file_path' => $this->uploadImage($file),
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ];
                }
            }

            if (! empty($fileRecords)) {
                $this->repository->attachFiles($maintenance, $fileRecords);
            }
        }

        $this->repository->logStatusChange($maintenance, [
            'old_status' => null,
            'new_status' => 'pending',
            'note'       => 'Request created.',
            'changed_at' => now(),
            'changed_by' => $data['requested_by'],
        ]);

        $created = $this->repository->find($maintenance->id);

        event(new MaintenanceRequestCreated($created));

        return $created;
            }

    public function getList(array $filters = [])
    {
        return $this->repository->getFiltered($filters);
    }

    public function getDetails(int $id, array $filters = [])
    {
        $maintenance = $this->repository->find($id);

        if (isset($filters['mosque_id']) && $maintenance->mosque_id !== $filters['mosque_id']) {
            abort(403, __('messages.maintenance.unauthorized'));
        }

        return $maintenance;
    }

    public function trackRequest(string $maintenanceNumber)
    {
        return $this->repository->findByMaintenanceNumber($maintenanceNumber);
    }

    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $maintenance = $this->repository->find($id);

        foreach ($maintenance->files as $file) {
            $this->deleteImage($file->file_path);
        }

        $this->repository->delete($id);
    }

    public function getPageStats(?int $mosqueId = null): array
    {
        $now   = now();
        $query = $mosqueId
            ? fn() => Maintenance::where('mosque_id', $mosqueId)
            : fn() => Maintenance::query();

        // طلبات مفتوحة
        $open = $query()
            ->where('status', 'pending')
            ->count();

        // جاري العمل
        $inProgress = $query()
            ->where('status', 'in_progress')
            ->count();

        $completedThisMonth = $query()
            ->where('status', 'completed')
            ->where(function ($q) use ($now) {
                $q->whereYear('completed_at',  $now->year)
                    ->whereMonth('completed_at', $now->month);
            })
            ->count();

        $critical = $query()
            ->where('priority', 'urgent')
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        return [
            'open_requests'        => $open,
            'in_progress'          => $inProgress,
            'completed_this_month' => $completedThisMonth,
            'critical'             => $critical,
        ];
    }

    public function getRecentRequests(?int $mosqueId = null, int $limit = 5): array
    {
        $query = Maintenance::with(['files']);
        if ($mosqueId) {
            $query->where('mosque_id', $mosqueId);
        }
        return $query->latest()->limit($limit)->get()->toArray();
    }

    public function getForAdmin(array $filters = [])
    {
        return $this->repository->getFiltered($filters);
    }

    public function updateStatus(int $id, string $newStatus, string $changedBy, ?string $note = null)
    {        $maintenance = $this->repository->find($id);
        $oldStatus   = $maintenance->status;

        $this->repository->update($id, [
            'status' => $newStatus,
            'notes'  => $note,
            'completed_at' => $newStatus === 'completed' ? now() : $maintenance->completed_at,
        ]);

        $this->repository->logStatusChange($maintenance, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note'       => $note,
            'changed_at' => now(),
            'changed_by' => $changedBy,
        ]);


        $updated = $this->repository->find($id);

        event(new MaintenanceStatusChanged($updated, $oldStatus, $newStatus, $note, $changedBy));

        return $updated;
            }

    public function getStatistics(array $filters = [])
    {
        $query = Maintenance::query();

        if (isset($filters['mosque_id'])) {
            $query->where('mosque_id', $filters['mosque_id']);
        }

        return [
            'total' => $query->count(),
            'by_status' => [
                'pending'     => (clone $query)->where('status', 'pending')->count(),
                'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
                'completed'   => (clone $query)->where('status', 'completed')->count(),
                'cancelled'   => (clone $query)->where('status', 'cancelled')->count(),
            ],
            'by_category' => $query->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category'),
            'by_priority' => $query->selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority'),
        ];
    }

    // ─── File Request Flow (Region Manager ⇄ Mosque Manager) ────────────────

    public function requestFiles(int $id, string $changedBy, string $note, int $adminId)
    {
        $maintenance = $this->repository->find($id);

        if (in_array($maintenance->status, ['completed', 'cancelled'])) {
            abort(422, __('messages.maintenance.cannot_request_files_final'));
        }

        $maintenance = $this->repository->requestFiles($id, [
            'files_requested'     => true,
            'files_requested_by'  => $adminId,
            'files_requested_at'  => now(),
            'files_request_note'  => $note,
        ]);

        $this->repository->logStatusChange($maintenance, [
            'old_status' => $maintenance->status,
            'new_status' => $maintenance->status,
            'note'       => __('messages.maintenance.files_requested_log', ['note' => $note]),
            'changed_at' => now(),
            'changed_by' => $changedBy,
        ]);

        return $this->repository->find($id);
    }

    public function uploadFiles(int $id, array $files, int $mosqueId, string $changedBy)
    {
        $maintenance = $this->repository->find($id);

        if ($maintenance->mosque_id !== $mosqueId) {
            abort(403, __('messages.maintenance.unauthorized'));
        }

        if (! $maintenance->files_requested) {
            abort(422, __('messages.maintenance.files_not_requested'));
        }

        $fileRecords = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $fileRecords[] = [
                    'file_path' => $this->uploadImage($file),
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ];
            }
        }

        if (! empty($fileRecords)) {
            $this->repository->attachFiles($maintenance, $fileRecords);
        }

        $maintenance = $this->repository->requestFiles($id, [
            'files_requested'     => false,
            'files_requested_by'  => null,
            'files_requested_at'  => null,
            'files_request_note'  => null,
        ]);

        $this->repository->logStatusChange($maintenance, [
            'old_status' => $maintenance->status,
            'new_status' => $maintenance->status,
            'note'       => __('messages.maintenance.files_uploaded_log'),
            'changed_at' => now(),
            'changed_by' => $changedBy,
        ]);

        return $this->repository->find($id);
    }

    public function getPendingFileRequests(?int $mosqueId = null, int $perPage = 15)
    {
        return $this->repository->getPendingFileRequests($mosqueId, $perPage);
    }

    // ─── Supabase Helpers ─────────────────────────────────────────────────────
    private function uploadImage(UploadedFile $file): string
    {
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.bucket');
        $key     = config('services.supabase.key');

        $path      = $bucket . '/' . $fileName;
        $uploadUrl = $baseUrl . '/storage/v1/object/' . $path;

        $fileContent = file_get_contents($file->getRealPath());

        $response = Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => $file->getMimeType(),
        ])->withBody(
            $fileContent,
            $file->getMimeType()
        )->post($uploadUrl);

        if (! $response->successful()) {

        \Illuminate\Support\Facades\Log::error('Supabase Upload Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Upload failed: ' . $response->body());
        }

        return $baseUrl . '/storage/v1/object/public/' . $path;
    }

    private function deleteImage(string $url): void
    {
        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.bucket');
        $key     = config('services.supabase.key');

        $fileName  = basename($url);
        $path      = $bucket . '/' . $fileName;
        $deleteUrl = $baseUrl . '/storage/v1/object/' . $path;

        Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->delete($deleteUrl);
    }
    public function getPublicList(array $filters = [])
    {
        return $this->repository->getPublicFiltered($filters);
    }

    public function getPublicDetails(int $id)
    {
        $maintenance = $this->repository->findPublic($id);

        if (! $maintenance) {
            abort(404, __('messages.maintenance.not_found'));
        }

        return $maintenance;
    }
}
