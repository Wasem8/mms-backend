<?php

namespace Modules\Volunteer\Services;

use Modules\Volunteer\DTOs\CreateOpportunityDTO;
use Modules\Volunteer\DTOs\UpdateOpportunityDTO;
use Modules\Volunteer\Events\ApplicationStatusChanged;
use Modules\Volunteer\Events\OpportunityCreated;
use Modules\Volunteer\Models\VolunteerApplication;
use Modules\Volunteer\Models\VolunteerOpportunity;
use Modules\Volunteer\Repositories\Contracts\VolunteerApplicationRepositoryInterface;
use Modules\Volunteer\Repositories\Contracts\VolunteerOpportunityRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VolunteerOpportunityService
{
    public function __construct(
        private readonly VolunteerOpportunityRepositoryInterface $opportunityRepo,
        private readonly VolunteerApplicationRepositoryInterface  $applicationRepo,
    ) {}

    // ─── Opportunities ────────────────────────────────────────────────────────

    public function listForManager(int $mosqueId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->opportunityRepo->findAllForManager($mosqueId, $perPage);
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
            event(new OpportunityCreated($opportunity));
            return $opportunity;
        });
    }

    public function update(VolunteerOpportunity $opportunity, UpdateOpportunityDTO $dto): VolunteerOpportunity
    {
        return DB::transaction(fn() => $this->opportunityRepo->update($opportunity, $dto));
    }

    public function close(VolunteerOpportunity $opportunity): VolunteerOpportunity
    {
        return DB::transaction(fn() => $this->opportunityRepo->close($opportunity));
    }

    // ─── Applications ─────────────────────────────────────────────────────────

    public function listApplications(int $opportunityId, int $perPage = 15): LengthAwarePaginator
    {
        $this->findOrFail($opportunityId);
        return $this->applicationRepo->findByOpportunity($opportunityId, $perPage);
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
