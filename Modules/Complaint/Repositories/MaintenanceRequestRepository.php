<?php

namespace Modules\Complaint\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Complaint\DTO\CreateMaintenanceRequestDTO;
use Modules\Complaint\DTO\ProcessMaintenanceRequestDTO;
use Modules\Complaint\Models\MaintenanceRequest;

class MaintenanceRequestRepository implements MaintenanceRequestRepositoryInterface
{

    public function __construct(
        private readonly MaintenanceRequest $model,
    ) {}

    public function listForMosque(
        int $mosqueId,
        ?string $status,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return $this->model
            ->with('mosque')
            ->where('mosque_id', $mosqueId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function listForAdmin(
        ?string $status,
        ?string $category,
        ?string $urgency,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return $this->model
            ->with('mosque')
            ->when($status,   fn($q) => $q->where('status', $status))
            ->when($category, fn($q) => $q->where('category', $category))
            ->when(
                ! is_null($urgency),
                fn($q) => $q->where('urgency', $urgency)
            )
            ->latest()
            ->paginate($perPage);
    }


    public function findById(int $id): MaintenanceRequest
    {
        return $this->model->with('mosque')->find($id);
    }

    public function findByReference(string $reference): MaintenanceRequest
    {
        return $this->model->with(['mosque', 'statusLogs', 'regionManager'])
            ->where('reference_number', $reference)
            ->first();
    }

    public function create(CreateMaintenanceRequestDTO $dto): MaintenanceRequest
    {
        $ref = $this->generateReference();

        $request = $this->model->create([
            'mosque_id'       => $dto->mosqueId,
            'title'           => $dto->title,
            'description'     => $dto->description,
            'category'        => $dto->category,
            'urgency'         => $dto->isUrgent,
            'attachments'     => $dto->attachments,
            'status'          => 'pending',
            'reference_number' => $ref,
        ]);

        return $request->fresh(['mosque']);
    }

    private function generateReference(): string
    {
        // Generate a 6-digit numeric reference with MR- prefix; ensure uniqueness
        do {
            $num = random_int(1, 999999);
            $ref = sprintf('MR-%06d', $num);
        } while ($this->model->where('reference_number', $ref)->exists());

        return $ref;
    }

    public function process(
        MaintenanceRequest $request,
        ProcessMaintenanceRequestDTO $dto,
    ): MaintenanceRequest {
        $oldStatus = $request->status;

        $request->update([
            'status'             => $dto->status,
            'rejection_reason'   => $dto->rejectionReason,
            'region_manager_id'  => $dto->regionManagerId,
        ]);

        // Log status change when status actually changed
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
}
