<?php

declare(strict_types=1);

namespace Modules\Mosque\Filters;

use Illuminate\Database\Eloquent\Builder;

class MosqueFilters
{

    public function apply(Builder $query, array $filters): Builder
    {
        if (isset($filters['city'])) {
            $this->filterByCity($query, $filters['city']);
        }

        if (isset($filters['district'])) {
            $this->filterByDistrict($query, $filters['district']);
        }

        if (isset($filters['status'])) {
            $this->filterByStatus($query, $filters['status']);
        }

        if (isset($filters['is_featured'])) {
            $this->filterByFeatured($query, $filters['is_featured']);
        }

        if (isset($filters['search'])) {
            $this->filterBySearch($query, $filters['search']);
        }

        if (isset($filters['facility_id'])) {
            $this->filterByFacility($query, $filters['facility_id']);
        }

        if (isset($filters['min_rating'])) {
            $this->filterByMinRating($query, (float) $filters['min_rating']);
        }

        if (isset($filters['has_imam'])) {
            $this->filterByHasImam($query, (bool) $filters['has_imam']);
        }

        if (isset($filters['sort_by'])) {
            $this->applySorting($query, $filters['sort_by'], $filters['sort_order'] ?? 'asc');
        }

        return $query;
    }


    private function filterByCity(Builder $query, string $city): void
    {
        $query->where('city', 'LIKE', "%{$city}%");
    }


    private function filterByDistrict(Builder $query, string $district): void
    {
        $query->where('district', 'LIKE', "%{$district}%");
    }


    private function filterByStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }


    private function filterByFeatured(Builder $query, bool $isFeatured): void
    {
        $query->where('is_featured', $isFeatured);
    }


    private function filterBySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('district', 'LIKE', "%{$search}%")
                ->orWhere('city', 'LIKE', "%{$search}%");
        });
    }


    private function filterByFacility(Builder $query, int|array $facilityId): void
    {
        $facilityIds = is_array($facilityId) ? $facilityId : [$facilityId];

        $query->whereHas('facilities', function (Builder $q) use ($facilityIds) {
            $q->whereIn('facilities.id', $facilityIds);
        });
    }

    private function filterByMinRating(Builder $query, float $minRating): void
    {
        $query->where('average_rating', '>=', $minRating);
    }


    private function filterByHasImam(Builder $query, bool $hasImam): void
    {
        if ($hasImam) {
            $query->whereNotNull('imam_id');
        } else {
            $query->whereNull('imam_id');
        }
    }


    private function applySorting(Builder $query, string $sortBy, string $sortOrder = 'asc'): void
    {
        $allowedSortFields = [
            'name',
            'city',
            'district',
            'average_rating',
            'reviews_count',
            'created_at',
        ];

        if (in_array($sortBy, $allowedSortFields, true)) {
            $direction = in_array(strtolower($sortOrder), ['asc', 'desc'], true)
                ? strtolower($sortOrder)
                : 'asc';

            $query->orderBy($sortBy, $direction);
        }
    }
}
