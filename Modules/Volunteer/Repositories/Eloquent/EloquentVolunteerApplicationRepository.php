<?php

namespace Modules\Volunteer\Repositories\Eloquent;

use Modules\Volunteer\Enums\ApplicationStatus;
use Modules\Volunteer\Models\VolunteerApplication;
use Modules\Volunteer\Repositories\Contracts\VolunteerApplicationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentVolunteerApplicationRepository implements VolunteerApplicationRepositoryInterface
{
    public function __construct(
        private readonly VolunteerApplication $model
    ) {}

    #[\Override]
    public function findById(int $id): ?VolunteerApplication
    {
        return $this->model->with(['opportunity', 'volunteer'])->find($id);
    }

    #[\Override]
    public function findByOpportunity(int $opportunityId, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('opportunity_id', $opportunityId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with('volunteer')
            ->latest()
            ->paginate($perPage);
    }

    #[\Override]
    public function findByVolunteer(int $volunteerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('volunteer_id', $volunteerId)
            ->with('opportunity')
            ->latest()
            ->paginate($perPage);
    }

    #[\Override]
    public function findPendingByVolunteerAndOpportunity(int $volunteerId, int $opportunityId): ?VolunteerApplication
    {
        return $this->model
            ->where('volunteer_id', $volunteerId)
            ->where('opportunity_id', $opportunityId)
            ->where('status', ApplicationStatus::Pending)
            ->first();
    }

    #[\Override]
    public function create(int $opportunityId, int $volunteerId): VolunteerApplication
    {
        return $this->model->create([
            'opportunity_id' => $opportunityId,
            'volunteer_id'   => $volunteerId,
            'status'         => ApplicationStatus::Pending,
        ]);
    }

    #[\Override]
    public function approve(VolunteerApplication $application): VolunteerApplication
    {
        $application->update(['status' => ApplicationStatus::Approved]);
        return $application->fresh();
    }

    #[\Override]
    public function reject(VolunteerApplication $application): VolunteerApplication
    {
        $application->update(['status' => ApplicationStatus::Rejected]);
        return $application->fresh();
    }
}
