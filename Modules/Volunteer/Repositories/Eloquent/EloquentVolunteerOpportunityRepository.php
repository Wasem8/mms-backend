<?php

namespace Modules\Volunteer\Repositories\Eloquent;

use Modules\Volunteer\DTOs\CreateOpportunityDTO;
use Modules\Volunteer\DTOs\UpdateOpportunityDTO;
use Modules\Volunteer\Enums\OpportunityStatus;
use Modules\Volunteer\Models\VolunteerOpportunity;
use Modules\Volunteer\Repositories\Contracts\VolunteerOpportunityRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentVolunteerOpportunityRepository implements VolunteerOpportunityRepositoryInterface
{
    public function __construct(
        private readonly VolunteerOpportunity $model
    ) {}

    #[\Override]
    public function findById(int $id): ?VolunteerOpportunity
    {
        return $this->model->withCount(['applications' => fn($q) => $q->where('status', 'approved')])->find($id);
    }

    #[\Override]
    public function findAllOpen(int $mosqueId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->withCount(['applications' => fn($q) => $q->where('status', 'approved')])
            ->where('mosque_id', $mosqueId)
            ->where('status', OpportunityStatus::Open)
            ->where('end_date', '>=', now()->toDateString())
            ->latest()
            ->paginate($perPage);
    }

    #[\Override]
    public function findAllForManager(?int $mosqueId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->model
            ->withCount(['applications' => fn($q) => $q->where('status', 'approved')])
            ->with('tasks')
            ->when($mosqueId, fn($q) => $q->where('mosque_id', $mosqueId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    #[\Override]
    public function create(CreateOpportunityDTO $dto): VolunteerOpportunity
    {
        return $this->model->create([
            'mosque_id'           => $dto->mosqueId,
            'title'               => $dto->title,
            'description'         => $dto->description,
            'required_volunteers' => $dto->requiredVolunteers,
            'start_date'          => $dto->startDate,
            'end_date'            => $dto->endDate,
            'status'              => OpportunityStatus::Open,
        ]);
    }

    #[\Override]
    public function update(VolunteerOpportunity $opportunity, UpdateOpportunityDTO $dto): VolunteerOpportunity
    {
        $opportunity->update(array_filter([
            'title'               => $dto->title,
            'description'         => $dto->description,
            'required_volunteers' => $dto->requiredVolunteers,
            'start_date'          => $dto->startDate,
            'end_date'            => $dto->endDate,
        ], fn($v) => ! is_null($v)));

        return $opportunity->fresh();
    }

    #[\Override]
    public function close(VolunteerOpportunity $opportunity): VolunteerOpportunity
    {
        $opportunity->update(['status' => OpportunityStatus::Closed]);
        return $opportunity->fresh();
    }
}
