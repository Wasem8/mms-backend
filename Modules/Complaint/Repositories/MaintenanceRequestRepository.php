<?php

namespace Modules\Complaint\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Complaint\DTO\ProcessMaintenanceRequestDTO;
use Modules\Complaint\Models\MaintenanceRequest;

class MaintenanceRequestRepository implements MaintenanceRequestRepositoryInterface
{
    public function __construct(
        private readonly MaintenanceRequest $model,
    ) {}

    public function findByMosque(
        int     $mosqueId,
        ?string $status  = null,
        int     $perPage = 15,
    ): LengthAwarePaginator {
        return $this->model
            ->with('mosque')
            ->where('mosque_id', $mosqueId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function listForAdmin(
        ?string $status   = null,
        ?string $category = null,
        ?string $urgency  = null,
        int     $perPage  = 15,
    ): LengthAwarePaginator {
        return $this->model
            ->with('mosque')
            ->when($status,   fn($q) => $q->where('status', $status))
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($urgency,  fn($q) => $q->where('urgency', $urgency))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?MaintenanceRequest
    {
        return $this->model->with('mosque')->find($id);
    }

    public function findByReference(string $reference): ?MaintenanceRequest
    {
        return $this->model
            ->with(['mosque', 'statusLogs', 'regionManager'])
            ->where('reference_number', $reference)
            ->first();
    }

    public function create(array $data): MaintenanceRequest
    {
        return $this->model->create([
            'reference_number' => $this->generateReference(),
            'mosque_id'        => $data['mosque_id'],
            'title'            => $data['title'],
            'description'      => $data['description'],
            'category'         => $data['category'],
            'urgency'          => $data['urgency'],
            'status'           => 'pending',
        ]);
    }

    public function attachFile(MaintenanceRequest $request, array $fileData): void
    {
        $request->files()->create($fileData);
    }

    public function process(
        MaintenanceRequest           $request,
        ProcessMaintenanceRequestDTO $dto,
    ): MaintenanceRequest {
        $oldStatus = $request->status;

        $request->update([
            'status'            => $dto->status,
            'rejection_reason'  => $dto->rejectionReason,
            'region_manager_id' => $dto->regionManagerId,
        ]);

        if ($oldStatus !== $dto->status) {
            $this->logStatusChange($request, [
                'old_status' => $oldStatus,
                'new_status' => $dto->status,
                'note'       => $dto->rejectionReason,
                'changed_by' => $dto->regionManagerId,
                'changed_at' => now(),
            ]);
        }

        return $request->fresh(['mosque']);
    }

    public function logStatusChange(MaintenanceRequest $request, array $logData): void
    {
        $request->statusLogs()->create($logData);
    }

    private function generateReference(): string
    {
        do {
            $ref = sprintf('MR-%06d', random_int(1, 999999));
        } while ($this->model->where('reference_number', $ref)->exists());

        return $ref;
    }
}
