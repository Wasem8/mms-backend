<?php

namespace Modules\MaintenanceRequest\Repositories;


use Modules\MaintenanceRequest\Models\Maintenance;

interface MaintenanceRepositoryInterface
{
    public function create(array $data): Maintenance;

    public function find(int $id): Maintenance;

    public function findByMaintenanceNumber(string $number): ?Maintenance;

    public function update(int $id, array $data): Maintenance;

    public function delete(int $id): bool;

    public function getFiltered(array $filters = []);

    public function attachFiles(Maintenance $maintenance, array $fileRecords): void;

    public function logStatusChange(Maintenance $maintenance, array $logData): void;

    public function getPublicFiltered(array $filters = []);

    public function findPublic(int $id): ?Maintenance;
}
