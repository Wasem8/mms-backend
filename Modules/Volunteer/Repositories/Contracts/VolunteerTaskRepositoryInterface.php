<?php

namespace Modules\Volunteer\Repositories\Contracts;

use Modules\Volunteer\DTOs\AssignTaskDTO;
use Modules\Volunteer\Models\VolunteerTask;
use Illuminate\Pagination\LengthAwarePaginator;

interface VolunteerTaskRepositoryInterface
{
    public function findById(int $id): ?VolunteerTask;

    public function findByApplication(int $applicationId): \Illuminate\Database\Eloquent\Collection;

    public function create(AssignTaskDTO $dto): VolunteerTask;

    public function markCompleted(VolunteerTask $task): VolunteerTask;
}
