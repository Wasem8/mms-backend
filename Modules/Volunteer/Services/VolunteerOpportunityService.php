<?php

namespace Modules\Volunteer\Services;

use Modules\Volunteer\DTOs\CreateOpportunityDTO;
use Modules\Volunteer\DTOs\CreateTaskDTO;
use Modules\Volunteer\DTOs\UpdateOpportunityDTO;
use Modules\Volunteer\Enums\TaskStatus;
use Modules\Volunteer\Events\ApplicationStatusChanged;
use Modules\Volunteer\Events\OpportunityCreated;
use Modules\Volunteer\Models\VolunteerApplication;
use Modules\Volunteer\Models\VolunteerOpportunity;
use Modules\Volunteer\Repositories\Contracts\VolunteerApplicationRepositoryInterface;
use Modules\Volunteer\Repositories\Contracts\VolunteerOpportunityRepositoryInterface;
use Modules\Volunteer\Repositories\Contracts\VolunteerTaskRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VolunteerOpportunityService
{
    public function __construct(
        private readonly VolunteerOpportunityRepositoryInterface $opportunityRepo,
        private readonly VolunteerApplicationRepositoryInterface  $applicationRepo,
        private readonly VolunteerTaskRepositoryInterface         $taskRepo,
    ) {}

    // ─── Opportunities ────────────────────────────────────────────────────────

    public function listForManager(?int $mosqueId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->opportunityRepo->findAllForManager($mosqueId, $perPage, $search, $status);
    }

    public function listOpen(int $mosqueId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->opportunityRepo->findAllOpen($mosqueId, $perPage);
    }

    public function findOrFail(int $id): VolunteerOpportunity
    {
        return $this->opportunityRepo->findById($id)
            ?? throw new ModelNotFoundException('VolunteerOpportunity');
    }

    public function create(CreateOpportunityDTO $dto): VolunteerOpportunity
    {
        return DB::transaction(function () use ($dto): VolunteerOpportunity {
            $opportunity = $this->opportunityRepo->create($dto);

            foreach ($dto->tasks as $taskDescription) {
                $this->taskRepo->create(new CreateTaskDTO(
                    opportunityId: $opportunity->id,
                    taskDescription: $taskDescription,
                ));
            }

            event(new OpportunityCreated($opportunity));
            return $opportunity->load('tasks');
        });
    }

    public function update(VolunteerOpportunity $opportunity, UpdateOpportunityDTO $dto): VolunteerOpportunity
    {
        return DB::transaction(function () use ($opportunity, $dto): VolunteerOpportunity {
            $updated = $this->opportunityRepo->update($opportunity, $dto);

            if ($dto->tasks !== null) {
                $this->syncTasks($updated, $dto->tasks);
            }

            return $updated->load('tasks');
        });
    }

    /**
     * Reconcile the opportunity's task list with the provided descriptions.
     * - Creates unassigned tasks for new descriptions.
     * - Removes only still-unassigned tasks whose description is no longer present
     *   (assigned/completed tasks are preserved to avoid losing assignment data).
     */
    private function syncTasks(VolunteerOpportunity $opportunity, array $descriptions): void
    {
        $newSet = array_values(array_unique(
            array_filter($descriptions, fn($d) => is_string($d) && trim($d) !== '')
        ));

        $existing = $this->taskRepo->findByOpportunity($opportunity->id);
        $existingByDesc = $existing->keyBy('task_description');

        foreach ($newSet as $desc) {
            if (! $existingByDesc->has($desc)) {
                $this->taskRepo->create(new CreateTaskDTO(
                    opportunityId: $opportunity->id,
                    taskDescription: $desc,
                ));
            }
        }

        foreach ($existing as $task) {
            if ($task->status === TaskStatus::Unassigned
                && ! in_array($task->task_description, $newSet, true)
            ) {
                $task->delete();
            }
        }
    }

    public function close(VolunteerOpportunity $opportunity): VolunteerOpportunity
    {
        return DB::transaction(fn() => $this->opportunityRepo->close($opportunity));
    }

    // ─── Applications ─────────────────────────────────────────────────────────

    public function listApplications(int $opportunityId, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $this->findOrFail($opportunityId);
        return $this->applicationRepo->findByOpportunity($opportunityId, $status, $perPage);
    }

    public function listMyApplications(int $volunteerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->applicationRepo->findByVolunteer($volunteerId, $perPage);
    }

    public function apply(int $opportunityId, int $volunteerId): VolunteerApplication
    {
        $opportunity = $this->findOrFail($opportunityId);

        if ($opportunity->status->value === 'closed') {
            throw ValidationException::withMessages([
                'opportunity' => __('messages.opportunity_closed_app'),
            ]);
        }

        $existing = $this->applicationRepo->findPendingByVolunteerAndOpportunity($volunteerId, $opportunityId);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'application' => __('messages.pending_application_exists'),
            ]);
        }

        return DB::transaction(fn() => $this->applicationRepo->create($opportunityId, $volunteerId));
    }

    public function approveApplication(int $applicationId): VolunteerApplication
    {
        $application = $this->findApplicationOrFail($applicationId);

        return DB::transaction(function () use ($application): VolunteerApplication {
            $updated = $this->applicationRepo->approve($application);

            $opportunity = $this->opportunityRepo->findById($application->opportunity_id);
            if ($opportunity && $opportunity->available_slots <= 0) {
                $this->opportunityRepo->close($opportunity);
            }

            event(new ApplicationStatusChanged($updated));
            return $updated;
        });
    }

    public function rejectApplication(int $applicationId): VolunteerApplication
    {
        $application = $this->findApplicationOrFail($applicationId);

        return DB::transaction(function () use ($application): VolunteerApplication {
            $updated = $this->applicationRepo->reject($application);
            event(new ApplicationStatusChanged($updated));
            return $updated;
        });
    }

    private function findApplicationOrFail(int $id): VolunteerApplication
    {
        return $this->applicationRepo->findById($id)
            ?? throw new ModelNotFoundException('VolunteerApplication');
    }
}
