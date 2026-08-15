<?php

namespace Modules\Volunteer\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Volunteer\DTOs\CreateTaskDTO;
use Modules\Volunteer\Enums\ApplicationStatus;
use Modules\Volunteer\Models\VolunteerTask;
use Modules\Volunteer\Repositories\Contracts\VolunteerApplicationRepositoryInterface;
use Modules\Volunteer\Repositories\Contracts\VolunteerOpportunityRepositoryInterface;
use Modules\Volunteer\Repositories\Contracts\VolunteerTaskRepositoryInterface;

class VolunteerTaskService
{
    public function __construct(
        private readonly VolunteerTaskRepositoryInterface $taskRepo,
        private readonly VolunteerOpportunityRepositoryInterface $opportunityRepo,
        private readonly VolunteerApplicationRepositoryInterface $applicationRepo,
    ) {}

    /** Manager: all tasks (assigned + unassigned) for an opportunity */
    public function listForOpportunity(int $opportunityId): Collection
    {
        $this->findOpportunityOrFail($opportunityId);

        return $this->taskRepo->findByOpportunity($opportunityId);
    }

    /** Volunteer: tasks assigned to their own application */
    public function listForApplication(int $applicationId, int $volunteerId): Collection
    {
        $application = $this->applicationRepo->findById($applicationId)
            ?? throw new ModelNotFoundException('VolunteerApplication');

        if ((int) $application->volunteer_id !== $volunteerId) {
            abort(403, __('messages.unauthorized_application_access'));
        }

        return $this->taskRepo->findByApplication($applicationId);
    }

    public function create(CreateTaskDTO $dto): VolunteerTask
    {
        $this->findOpportunityOrFail($dto->opportunityId);

        return DB::transaction(fn() => $this->taskRepo->create($dto));
    }

    public function assign(int $taskId, int $applicationId): VolunteerTask
    {
        $task = $this->findTaskOrFail($taskId);

        $application = $this->applicationRepo->findById($applicationId)
            ?? throw new ModelNotFoundException('VolunteerApplication');

        if ($application->opportunity_id !== $task->opportunity_id) {
            throw ValidationException::withMessages([
                'application_id' => __('messages.application_not_in_opportunity'),
            ]);
        }

        if ($application->status !== ApplicationStatus::Approved) {
            throw ValidationException::withMessages([
                'application_id' => __('messages.application_not_approved'),
            ]);
        }

        return DB::transaction(fn() => $this->taskRepo->assign($task, $applicationId));
    }

    /** Volunteer marks their own assigned task as completed */
    public function complete(int $taskId, int $volunteerId): VolunteerTask
    {
        $task = $this->findTaskOrFail($taskId);

        if (! $task->application || (int) $task->application->volunteer_id !== $volunteerId) {
            abort(403, __('messages.unauthorized_task_access'));
        }

        return DB::transaction(fn() => $this->taskRepo->markCompleted($task));
    }

    private function findOpportunityOrFail(int $id)
    {
        return $this->opportunityRepo->findById($id)
            ?? throw new ModelNotFoundException('VolunteerOpportunity');
    }

    private function findTaskOrFail(int $id): VolunteerTask
    {
        return $this->taskRepo->findById($id)
            ?? throw new ModelNotFoundException('VolunteerTask');
    }
}
