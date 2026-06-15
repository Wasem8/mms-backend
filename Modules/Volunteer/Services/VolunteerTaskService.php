<?php

namespace Modules\Volunteer\Services;

use Modules\Volunteer\DTOs\AssignTaskDTO;
use Modules\Volunteer\Enums\ApplicationStatus;
use Modules\Volunteer\Models\VolunteerTask;
use Modules\Volunteer\Repositories\Contracts\VolunteerApplicationRepositoryInterface;
use Modules\Volunteer\Repositories\Contracts\VolunteerTaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VolunteerTaskService
{
    public function __construct(
        private readonly VolunteerTaskRepositoryInterface        $taskRepo,
        private readonly VolunteerApplicationRepositoryInterface $applicationRepo,
    ) {}

    public function listForApplication(int $applicationId): Collection
    {
        return $this->taskRepo->findByApplication($applicationId);
    }

    public function assign(AssignTaskDTO $dto): VolunteerTask
    {
        $application = $this->applicationRepo->findById($dto->applicationId)
            ?? throw new ModelNotFoundException('VolunteerApplication');

        if ($application->status !== ApplicationStatus::Approved) {
            throw ValidationException::withMessages([
                'application' => __('messages.task_only_approved'),
            ]);
        }

        return DB::transaction(fn() => $this->taskRepo->create($dto));
    }

    public function markCompleted(int $taskId): VolunteerTask
    {
        $task = $this->taskRepo->findById($taskId)
            ?? throw new ModelNotFoundException('VolunteerTask');

        return DB::transaction(fn() => $this->taskRepo->markCompleted($task));
    }
}
