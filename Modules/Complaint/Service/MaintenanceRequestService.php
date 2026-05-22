<?php

namespace Modules\Complaint\Service;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Complaint\DTO\CreateMaintenanceRequestDTO;
use Modules\Complaint\DTO\ProcessMaintenanceRequestDTO;
use Modules\Complaint\Repositories\MaintenanceRequestRepositoryInterface;
use Modules\Complaint\Models\MaintenanceRequest;


class MaintenanceRequestService
{
    public function __construct(
        private readonly MaintenanceRequestRepositoryInterface $repository,
    ) {}

    public function listForMosque(
        int     $mosqueId,
        ?string $status,
        int     $perPage = 15,
    ): LengthAwarePaginator {
        return $this->repository->findByMosque(
            mosqueId: $mosqueId,
            status: $status,
            perPage: $perPage,
        );
    }
    public function create(CreateMaintenanceRequestDTO $dto): MaintenanceRequest
    {
        $complaint = $this->repository->create($dto);

        if (! empty($dto->attachments)) {
            $fileRecords = [];

            foreach ($dto->attachments as $file) {
                if ($file instanceof UploadedFile) {
                    $fileRecords[] = [
                        'file'      => $this->uploadImage($file),
                        'file_type' => $file->getClientMimeType(),
                    ];
                }
            }

            if (! empty($fileRecords)) {
                $this->repository->attachFiles($complaint, $fileRecords);
            }
        }

        return $complaint->load('files');
    }


    public function listForAdmin(
        ?string $status   = null,
        ?string $category = null,
        ?string $urgency  = null,
        int     $perPage  = 15,
    ): LengthAwarePaginator {
        return $this->repository->listForAdmin($status, $category, $urgency, $perPage);
    }

    public function findOrFail(int $id): MaintenanceRequest
    {
        return $this->repository->findById($id)
            ?? throw new ModelNotFoundException('MaintenanceRequest');
    }

    public function findByReference(string $reference): MaintenanceRequest
    {
        return $this->repository->findByReference($reference)
            ?? throw new ModelNotFoundException('Maintenance request not found.');
    }

    public function buildStatusHistory(MaintenanceRequest $request): array
    {
        $initial = [
            'status' => 'pending',
            'date'   => $request->created_at,
            'note'   => 'Request created',
        ];

        $logs = $request->statusLogs->map(fn($log) => [
            'status' => $log->new_status,
            'date'   => $log->changed_at,
            'note'   => $log->note,
        ])->all();

        return array_merge([$initial], $logs);
    }

    public function process(
        MaintenanceRequest $maintenanceRequest,
        ProcessMaintenanceRequestDTO $dto,
    ): MaintenanceRequest {
        return DB::transaction(
            fn(): MaintenanceRequest => $this->repository->process($maintenanceRequest, $dto)
        );
    }

   private function uploadImage(UploadedFile $image): string
{
    $fileName  = uniqid() . '.' . $image->getClientOriginalExtension();
    $mimeType  = $image->getMimeType();

    $baseUrl = config('services.supabase.url');
    $bucket  = config('services.supabase.bucket');
    $key     = config('services.supabase.key');

    $uploadUrl = "{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}";

    $response = Http::withHeaders([
        'apikey'        => $key,
        'Authorization' => 'Bearer ' . $key,
        'Content-Type'  => $mimeType,         // ← required by Supabase
        'x-upsert'      => 'false',           // ← reject duplicates
    ])->withBody(
        file_get_contents($image->getRealPath()), // ← raw binary, not multipart
        $mimeType
    )->post($uploadUrl);

    if (! $response->successful()) {
        throw new \RuntimeException(
            'Supabase upload failed [' . $response->status() . ']: ' . $response->body()
        );
    }

    return "{$baseUrl}/storage/v1/object/public/{$bucket}/{$fileName}";
}

    private function deleteImage(string $url): void
    {
        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.bucket');
        $key     = config('services.supabase.key');

        $fileName  = basename(parse_url($url, PHP_URL_PATH)); // safer than basename($url)
        $deleteUrl = "{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}";

        Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->delete($deleteUrl);
    }
}
