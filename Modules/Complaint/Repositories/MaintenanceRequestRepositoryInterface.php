<?php

namespace Modules\Complaint\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Complaint\DTO\ProcessMaintenanceRequestDTO;
use Modules\Complaint\Models\MaintenanceRequest;

interface MaintenanceRequestRepositoryInterface
{
    public function findByMosque(
        int     $mosqueId,
        ?string $status,
        int     $perPage = 15,
    ): LengthAwarePaginator;

    public function listForAdmin(
        ?string $status   = null,
        ?string $category = null,
        ?string $urgency  = null,
        int     $perPage  = 15,
    ): LengthAwarePaginator;

    public function findById(int $id): ?MaintenanceRequest;

    public function findByReference(string $reference): ?MaintenanceRequest;

    public function create(array $data): MaintenanceRequest;

    public function attachFile(MaintenanceRequest $request, array $fileData): void;

    public function process(
        MaintenanceRequest           $request,
        ProcessMaintenanceRequestDTO $dto,
    ): MaintenanceRequest;

    public function logStatusChange(MaintenanceRequest $request, array $logData): void;
}
