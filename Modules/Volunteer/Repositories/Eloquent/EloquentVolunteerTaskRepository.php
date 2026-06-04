<?php

namespace Modules\Volunteer\Repositories\Eloquent;

use Modules\Volunteer\DTOs\AssignTaskDTO;
use Modules\Volunteer\Enums\TaskStatus;
use Modules\Volunteer\Models\VolunteerTask;
use Modules\Volunteer\Repositories\Contracts\VolunteerTaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentVolunteerTaskRepository implements VolunteerTaskRepositoryInterface
{
    public function __construct(
        private readonly VolunteerTask $model
    ) {}

    #[\Override]
    public function findById(int $id): ?VolunteerTask
    {
        return $this->model->with('application')->find($id);
    }

    #[\Override]
    public function findByApplication(int $applicationId): Collection
    {
        return $this->model
            ->where('application_id', $applicationId)
            ->latest()
            ->get();
    }

    #[\Override]
    public function create(AssignTaskDTO $dto): VolunteerTask
    {
        return $this->model->create([
            'application_id'   => $dto->applicationId,
            'task_description' => $dto->taskDescription,
            'status'           => TaskStatus::Assigned,
        ]);
    }

    #[\Override]
    public function markCompleted(VolunteerTask $task): VolunteerTask
    {
        $task->update(['status' => TaskStatus::Completed]);
        return $task->fresh();
    }
}
