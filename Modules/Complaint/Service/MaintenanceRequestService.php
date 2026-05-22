<?php

namespace Modules\Complaint\Service;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $attachmentUrls = $this->uploadImage($dto->attachments);

        return DB::transaction(
            fn() => $this->repository->create($dto, $attachmentUrls)
        );
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
}
