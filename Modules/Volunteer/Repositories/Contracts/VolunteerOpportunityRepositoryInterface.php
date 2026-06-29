<?php

namespace Modules\Volunteer\Repositories\Contracts;

use Modules\Volunteer\DTOs\CreateOpportunityDTO;
use Modules\Volunteer\DTOs\UpdateOpportunityDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Volunteer\Models\VolunteerOpportunity;
interface VolunteerOpportunityRepositoryInterface
{
    public function findById(int $id): ?VolunteerOpportunity;

    public function findAllOpen(int $mosqueId, int $perPage = 15): LengthAwarePaginator;

    public function findAllForManager(int $mosqueId, int $perPage = 15): LengthAwarePaginator;

    public function create(CreateOpportunityDTO $dto): VolunteerOpportunity;

    public function update(VolunteerOpportunity $opportunity, UpdateOpportunityDTO $dto): VolunteerOpportunity;

    public function close(VolunteerOpportunity $opportunity): VolunteerOpportunity;
}
