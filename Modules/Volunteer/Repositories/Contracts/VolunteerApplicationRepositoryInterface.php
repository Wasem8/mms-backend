<?php

namespace Modules\Volunteer\Repositories\Contracts;

use Modules\Volunteer\Models\VolunteerApplication;
use Illuminate\Pagination\LengthAwarePaginator;

interface VolunteerApplicationRepositoryInterface
{
    public function findById(int $id): ?VolunteerApplication;

    public function findByOpportunity(int $opportunityId, int $perPage = 15): LengthAwarePaginator;

    public function findByVolunteer(int $volunteerId, int $perPage = 15): LengthAwarePaginator;

    public function findPendingByVolunteerAndOpportunity(int $volunteerId, int $opportunityId): ?VolunteerApplication;

    public function create(int $opportunityId, int $volunteerId): VolunteerApplication;

    public function approve(VolunteerApplication $application): VolunteerApplication;

    public function reject(VolunteerApplication $application): VolunteerApplication;
}
