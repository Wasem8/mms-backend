<?php

namespace Modules\MaintenanceRequest\Repositories;


use Illuminate\Support\Collection;
use Modules\MaintenanceRequest\Models\Maintenance;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function __construct(
        private readonly Maintenance $model,
    ) {}

    public function create(array $data): Maintenance
    {
        return Maintenance::create($data);
    }

    public function find(int $id): Maintenance
    {
        return Maintenance::with(['files', 'statusLogs'])->findOrFail($id);
    }

    public function findByMaintenanceNumber(string $number): ?Maintenance
    {
        return Maintenance::with(['statusLogs'])
            ->where('maintenance_number', $number)
            ->first();
    }

    public function update(int $id, array $data): Maintenance
    {
        $maintenance = $this->find($id);
        $maintenance->update($data);

        return $maintenance->fresh(['files', 'statusLogs']);
    }

    public function delete(int $id): bool
    {
        return Maintenance::findOrFail($id)->delete();
    }

    public function getFiltered(array $filters = [])
    {
        return Maintenance::with(['files', 'statusLogs'])
            ->when(isset($filters['status']),    fn($q) => $q->where('status',    $filters['status']))
            ->when(isset($filters['category']),  fn($q) => $q->where('category',  $filters['category']))
            ->when(isset($filters['priority']),  fn($q) => $q->where('priority',  $filters['priority']))
            ->when(isset($filters['mosque_id']), fn($q) => $q->where('mosque_id', $filters['mosque_id']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function attachFiles(Maintenance $maintenance, array $fileRecords): void
    {
        $maintenance->files()->createMany($fileRecords);
    }

    public function logStatusChange(Maintenance $maintenance, array $logData): void
    {
        $maintenance->statusLogs()->create([
            'old_status' => $logData['old_status'],
            'new_status' => $logData['new_status'],
            'changed_by' => $logData['changed_by'],
            'notes'      => $logData['note'] ?? null,
        ]);
    }
}
