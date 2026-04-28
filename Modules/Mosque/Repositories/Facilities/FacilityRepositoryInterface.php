<?php

namespace Modules\Facility\Repositories\Facilites;

use Illuminate\Support\Collection;
use Modules\Facility\Models\Facility;
use Modules\Mosque\Models\Facility as ModelsFacility;
use Modules\Mosque\Models\Mosque;

interface FacilityRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?ModelsFacility;

    public function getByMosque(int $mosqueId): Collection;

    public function syncToMosque(Mosque $mosque, array $facilityIds): void;

    public function attachToMosque(Mosque $mosque, array $facilityIds): void;

    public function detachFromMosque(Mosque $mosque, ?array $facilityIds = null): void;
}
