<?php

namespace Modules\Complaint\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Complaint\DTO\CreateMaintenanceRequestDTO;
use Modules\Complaint\DTO\ProcessMaintenanceRequestDTO;
use Modules\Complaint\Models\MaintenanceRequest as ModelsMaintenanceRequest;

interface MaintenanceRequestRepositoryInterface
{
    public function listForMosque(
        int $mosqueId,
        ?string $status,
        int $perPage = 15,
    ): LengthAwarePaginator;

    public function listForAdmin(
        ?string $status,
        ?string $category,
        ?string $urgency,
        int $perPage = 15,
    ): LengthAwarePaginator;


    public function findById(int $id): ModelsMaintenanceRequest;

    public function findByReference(string $reference): ModelsMaintenanceRequest;


    public function create(CreateMaintenanceRequestDTO $dto): ModelsMaintenanceRequest;

    public function process(
        ModelsMaintenanceRequest $request,
        ProcessMaintenanceRequestDTO $dto,
    ): ModelsMaintenanceRequest;

    public function logStatusChange(ModelsMaintenanceRequest $request, array $logData): void;
}
