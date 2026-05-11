<?php

namespace Modules\Mosque\Services;

use Illuminate\Support\Facades\DB;
use Modules\Mosque\Models\Facility;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Repositories\FacilityRepositoryInterface;

class FacilityService
{
    public function __construct(
        private readonly FacilityRepositoryInterface $facilityRepository
    ) {}

    public function createFacility(array $data): Facility
    {
        return  $this->facilityRepository->create($data);
    }

    public function updateFacility(Facility $facility, array $data): Facility
    {
        return $this->facilityRepository->update($facility, $data);
    }

    public function deleteFacility(Facility $facility): void
    {
        $this->facilityRepository->delete($facility);
    }

    public function getAllFacilities()
    {
        return $this->facilityRepository->getAll('id', 'name');
    }

    public function getFacilitiesByMosque(int $mosqueId)
    {
        return $this->facilityRepository->getByMosque($mosqueId);
    }

    public function syncMosqueFacilities(Mosque $mosque, array $facilityIds): void
    {
        $this->facilityRepository->syncToMosque($mosque, $facilityIds);
    }
    public function attachFacilitiesToMosque(Mosque $mosque, array $facilityIds): void
    {
        $this->facilityRepository->attachToMosque($mosque, $facilityIds);
    }
    public function detachFacilitiesFromMosque(Mosque $mosque, array $facilityIds): void
    {
        $mosque->facilities()->detach($facilityIds);
    }
}
