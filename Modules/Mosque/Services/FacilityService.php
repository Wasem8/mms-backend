<?php

namespace Modules\Facility\Services;

use Modules\Facility\Repositories\Facilites\FacilityRepositoryInterface;
use Modules\Mosque\Models\Mosque;

class FacilityService
{
    public function __construct(
        private readonly FacilityRepositoryInterface $facilityRepository
    ) {}

    public function getAllFacilities()
    {
        return $this->facilityRepository->getAll();
    }

    public function getFacilitiesByMosque(int $mosqueId)
    {
        return $this->facilityRepository->getByMosque($mosqueId);
    }

    public function syncMosqueFacilities(Mosque $mosque, array $facilityIds): void
    {
        $this->facilityRepository->syncToMosque($mosque, $facilityIds);
    }

    public function addFacilitiesToMosque(Mosque $mosque, array $facilityIds): void
    {
        $this->facilityRepository->attachToMosque($mosque, $facilityIds);
    }

    public function removeFacilitiesFromMosque(Mosque $mosque, ?array $facilityIds = null): void
    {
        $this->facilityRepository->detachFromMosque($mosque, $facilityIds);
    }
}
