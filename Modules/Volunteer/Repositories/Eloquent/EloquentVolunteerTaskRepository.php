<?php

namespace Modules\Volunteer\Repositories\Eloquent;

use Modules\Volunteer\DTOs\AssignTaskDTO;
use Modules\Volunteer\Enums\TaskStatus;
use Modules\Volunteer\Models\VolunteerTask;
use Modules\Volunteer\Repositories\Contracts\VolunteerTaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Modules\Volunteer\DTOs\CreateTaskDTO;

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

    public function findByOpportunity(int $opportunityId): Collection
    {
        return VolunteerTask::where('opportunity_id', $opportunityId)
            ->with('application.volunteer')
            ->latest()
            ->get();
    }
    
    #[\Override]
    public function findByApplication(int $applicationId): Collection
    {
        return $this->model
            ->where('application_id', $applicationId)
            ->latest()
            ->get();
    }

    public function create(CreateTaskDTO $dto): VolunteerTask
    {
        return VolunteerTask::create([
            'opportunity_id'   => $dto->opportunityId,
            'application_id'   => null,
            'task_description' => $dto->taskDescription,
            'status'           => TaskStatus::Unassigned,
        ]);
    }
    public function assign(VolunteerTask $task, int $applicationId): VolunteerTask
    {
        $task->update([
            'application_id' => $applicationId,
            'status'         => TaskStatus::Assigned,
        ]);

        return $task->fresh('application.volunteer');
    }

    #[\Override]
    public function markCompleted(VolunteerTask $task): VolunteerTask
    {
        $task->update(['status' => TaskStatus::Completed]);
        return $task->fresh();
    }
}
