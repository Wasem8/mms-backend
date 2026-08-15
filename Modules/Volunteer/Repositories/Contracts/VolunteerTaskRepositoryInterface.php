<?php

namespace Modules\Volunteer\Repositories\Contracts;

use Modules\Volunteer\DTOs\AssignTaskDTO;
use Modules\Volunteer\Models\VolunteerTask;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Volunteer\DTOs\CreateTaskDTO;


interface VolunteerTaskRepositoryInterface
{
    public function findById(int $id): ?VolunteerTask;

    public function findByApplication(int $applicationId): Collection;

    public function findByOpportunity(int $opportunityId): Collection;

    public function assign(VolunteerTask $task, int $applicationId): VolunteerTask;


    public function create(CreateTaskDTO $dto): VolunteerTask;

    public function markCompleted(VolunteerTask $task): VolunteerTask;
}
