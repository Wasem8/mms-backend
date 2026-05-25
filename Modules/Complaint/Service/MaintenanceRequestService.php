<?php

namespace Modules\Complaint\Service;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        ?string $status  = null,
        int     $perPage = 15,
    ): LengthAwarePaginator {
        return $this->repository->findByMosque(
            mosqueId: $mosqueId,
            status: $status,
            perPage: $perPage,
        );
    }

    public function create(array $data, array $files = []): array
    {
        $maintenanceRequest = $this->repository->create($data);

        $uploadedFiles = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $publicUrl = $this->uploadImage($file);

                $this->repository->attachFile($maintenanceRequest, [
                    'file'      => $publicUrl,
                    'file_type' => $file->getMimeType(),
                ]);

                $uploadedFiles[] = [
                    'url'       => $publicUrl,
                    'file_type' => $file->getMimeType(),
                ];
            }
        }

        $mosque = $maintenanceRequest->mosque;

        return [
            'id'               => $maintenanceRequest->id,
            'reference_number' => $maintenanceRequest->reference_number,
            'mosque_id'        => $maintenanceRequest->mosque_id,
            'title'            => $maintenanceRequest->title,
            'description'      => $maintenanceRequest->description,
            'category'         => $maintenanceRequest->category,
            'urgency'          => $maintenanceRequest->urgency,
            'status'           => $maintenanceRequest->status,
            'attachments'      => $uploadedFiles,
            'created_at'       => $maintenanceRequest->created_at->format('d M Y, h:i A'),
            'updated_at'       => $maintenanceRequest->updated_at->format('d M Y, h:i A'),
            'mosque'           => $mosque ? [
                'id'   => $mosque->id,
                'name' => $mosque->name,
            ] : null,
        ];
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
        MaintenanceRequest           $maintenanceRequest,
        ProcessMaintenanceRequestDTO $dto,
    ): MaintenanceRequest {
        return DB::transaction(
            fn(): MaintenanceRequest => $this->repository->process($maintenanceRequest, $dto)
        );
    }

    private function uploadImage(UploadedFile $image): string
    {
        $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
        $mimeType = $image->getMimeType();
        $baseUrl  = config('services.supabase.url');
        $bucket   = config('services.supabase.bucket');
        $key      = config('services.supabase.key');

        $response = Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => $mimeType,
            'x-upsert'      => 'false',
        ])->withBody(
            file_get_contents($image->getRealPath()),
            $mimeType
        )->post("{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}");

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

        $fileName  = basename(parse_url($url, PHP_URL_PATH));
        $deleteUrl = "{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}";

        Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->delete($deleteUrl);
    }
}
