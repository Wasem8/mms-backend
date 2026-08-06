<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueNeed;

class MosqueNeedRepository implements MosqueNeedRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return MosqueNeed::latest()->paginate($perPage);
    }

    public function getAllPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = MosqueNeed::query()
            ->with('mosque:id,name,city,latitude,longitude')
            ->select('mosque_needs.*')
            ->selectRaw('(target_amount - collected_amount) as funding_gap');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['urgent']) && $filters['urgent'] !== null) {
            $query->where('is_urgent', filter_var($filters['urgent'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['mosque_id'])) {
            $query->where('mosque_id', $filters['mosque_id']);
        }

        if (!empty($filters['city'])) {
            $query->whereHas('mosque', fn($q) => $q->where('city', $filters['city']));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['near'])) {
            [$lat, $lng] = $filters['near'];

            $query->join('mosques', 'mosques.id', '=', 'mosque_needs.mosque_id')
                ->whereNotNull('mosques.latitude')
                ->whereNotNull('mosques.longitude')
                ->selectRaw('
            (6371 * acos(
                cos(radians(?)) * cos(radians(mosques.latitude))
                * cos(radians(mosques.longitude) - radians(?))
                + sin(radians(?)) * sin(radians(mosques.latitude))
            )) as distance_km
        ', [$lat, $lng, $lat]);

            if (!empty($filters['radius_km'])) {
                $query->whereRaw('
            (6371 * acos(
                cos(radians(?)) * cos(radians(mosques.latitude))
                * cos(radians(mosques.longitude) - radians(?))
                + sin(radians(?)) * sin(radians(mosques.latitude))
            )) <= ?
        ', [$lat, $lng, $lat, $filters['radius_km']]);
            }
        }

        $sortOrder = $filters['sort_order'] ?? 'desc';

        match ($filters['sort_by'] ?? 'created_at') {
            'urgency'     => $query->orderBy('is_urgent', $sortOrder)->orderBy('mosque_needs.created_at', 'desc'),
            'funding_gap' => $query->orderBy('funding_gap', $sortOrder),
            'deadline'    => $query->orderBy('deadline', $sortOrder),
            'distance'    => $query->orderBy('distance_km', $sortOrder),
            default       => $query->orderBy('mosque_needs.created_at', $sortOrder),
        };

        return $query->paginate($perPage);
    }

    public function find(int $id): ?MosqueNeed
    {
        return MosqueNeed::find($id);
    }

    public function create(array $data): MosqueNeed
    {
        return MosqueNeed::create($data);
    }

    public function update(MosqueNeed $need, array $data): MosqueNeed
    {
        $need->update($data);
        return $need;
    }

    public function delete(MosqueNeed $need): bool
    {
        return $need->delete();
    }

    public function getNearbyMosquesWithNeeds(
        float $lat,
        float $lng,
        ?float $radiusKm,
        bool $urgentOnly,
        int $perPage
    ): LengthAwarePaginator {
        $query = Mosque::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->scopeNearby($lat, $lng)
            ->whereHas('needs', fn($q) => $q->where('status', 'open'));

        if ($urgentOnly) {
            $query->whereHas('needs', fn($q) => $q->where('status', 'open')->where('is_urgent', true));
        }

        $query->withCount([
            'needs as open_needs_count'        => fn($q) => $q->where('status', 'open'),
            'needs as open_urgent_needs_count' => fn($q) => $q->where('status', 'open')->where('is_urgent', true),
        ]);

        if ($radiusKm) {
            $query->whereRaw('
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?)) * sin(radians(latitude))
            )) <= ?
        ', [$lat, $lng, $lat, $radiusKm]);
        }

        return $query->paginate($perPage);
    }
}
