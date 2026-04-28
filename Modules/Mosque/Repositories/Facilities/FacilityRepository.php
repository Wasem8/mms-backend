<?php

namespace Modules\Facility\Repositories\Facilites;

use Illuminate\Support\Collection;
use Modules\Facility\Models\Facility;
use Modules\Mosque\Models\Facility as ModelsFacility;
use Modules\Mosque\Models\Mosque;

class FacilityRepository implements FacilityRepositoryInterface
{
    public function getAll(): Collection
    {
        return ModelsFacility::all();
    }

    public function findById(int $id): ?ModelsFacility
    {
        return ModelsFacility::find($id);
    }

    public function getByMosque(int $mosqueId): Collection
    {
        return ModelsFacility::whereHas('mosques', function ($q) use ($mosqueId) {
            $q->where('mosques.id', $mosqueId);
        })->get();
    }

    public function syncToMosque(Mosque $mosque, array $facilityIds): void
    {
        $mosque->facilities()->sync($facilityIds);
    }

    public function attachToMosque(Mosque $mosque, array $facilityIds): void
    {
        $mosque->facilities()->syncWithoutDetaching($facilityIds);
    }

    public function detachFromMosque(Mosque $mosque, ?array $facilityIds = null): void
    {
        if ($facilityIds === null) {
            $mosque->facilities()->detach();
            return;
        }

        $mosque->facilities()->detach($facilityIds);
    }
}
