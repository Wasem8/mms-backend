<?php

declare(strict_types=1);

namespace Modules\Mosque\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Mosque\Filters\MosqueFilters;
use Modules\Mosque\Models\Mosque;

class MosqueRepository implements MosqueRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        private readonly Mosque $model,
        private readonly MosqueFilters $filters
    ) {}


    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildBaseQuery();

        $query = $this->filters->apply($query, $filters);

        return $query->paginate($perPage);
    }


    public function getAll(array $filters = []): Collection
    {
        $query = $this->buildBaseQuery();

        $query = $this->filters->apply($query, $filters);

        return $query->get();
    }


    public function searchMosques(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $searchFilters = array_merge($filters, ['search' => $query]);

        return $this->getAllPaginated($searchFilters, $perPage);
    }


    public function findById(int $id, array $relations = []): ?Mosque
    {
        $query = $this->model->newQuery();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }


    public function create(array $data): Mosque
    {
        return $this->model->create($data);
    }

    public function update(Mosque $mosque, array $data): bool
    {
        return $mosque->update($data);
    }


    public function delete(Mosque $mosque): bool
    {
        return $mosque->delete();
    }


    public function syncFacilities(Mosque $mosque, array $facilityIds): array
    {
        return $mosque->facilities()->sync($facilityIds);
    }


    public function detachAllFacilities(Mosque $mosque): int
    {
        return $mosque->facilities()->detach();
    }


    public function getByCity(string $city): Collection
    {
        return $this->model->newQuery()
            ->with(['facilities', 'manager:id,name'])
            ->where('city', $city)
            ->orderBy('is_featured', 'desc')
            ->orderBy('average_rating', 'desc')
            ->get();
    }


    public function getFeatured(int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->with(['facilities','manager:id,name'])
            ->where('is_featured', true)
            ->where('status', 'active')
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();
    }

    public function updateRating(Mosque $mosque, float $averageRating, int $reviewsCount): bool
    {
        return $mosque->update([
            'average_rating' => $averageRating,
            'reviews_count' => $reviewsCount,
        ]);
    }


    private function buildBaseQuery(): Builder
    {
        return $this->model->newQuery()
            ->with([
                'facilities',
                'manager:id,name',
            ])
            ->latest();
    }
}
