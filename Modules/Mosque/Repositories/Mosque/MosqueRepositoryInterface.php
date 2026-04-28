<?php

declare(strict_types=1);

namespace Modules\Mosque\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Mosque\Models\Mosque;

interface MosqueRepositoryInterface
{

    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;


    public function getAll(array $filters = []): Collection;


    public function findById(int $id, array $relations = []): ?Mosque;


    public function create(array $data): Mosque;

    public function update(Mosque $mosque, array $data): bool;


    public function delete(Mosque $mosque): bool;

    public function syncFacilities(Mosque $mosque, array $facilityIds): array;


    public function detachAllFacilities(Mosque $mosque): int;


    public function getByCity(string $city): Collection;


    public function getFeatured(int $limit = 10): Collection;


    public function updateRating(Mosque $mosque, float $averageRating, int $reviewsCount): bool;
}
