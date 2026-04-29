<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Support\Collection;
use Modules\Mosque\Models\Facility as ModelFacility;
use Modules\Mosque\Models\Mosque;

interface FacilityRepositoryInterface
{

    public function create(array $data): ?ModelFacility;

    public function update(ModelFacility $facility, array $data): ModelFacility;

    public function delete(ModelFacility $facility): void;

    public function getAll(): Collection;

    public function findById(int $id): ?ModelFacility;

    public function getByMosque(int $mosqueId): Collection;

    public function syncToMosque(Mosque $mosque, array $facilityIds): void;

    public function attachToMosque(Mosque $mosque, array $facilityIds): void;

    public function detachFromMosque(Mosque $mosque, ?array $facilityIds = null): void;
}
