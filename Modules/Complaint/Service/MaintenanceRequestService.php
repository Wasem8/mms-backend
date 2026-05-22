<?php

namespace Modules\Complaint\Service;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(fn() => $this->repository->create($dto));
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
}
