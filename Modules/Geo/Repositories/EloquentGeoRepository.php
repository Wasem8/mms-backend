<?php

namespace Modules\Geo\Repositories;

use Modules\Geo\Repositories\GeoRepositoryInterface;
use Modules\Geo\Models\City;
use Modules\Geo\Models\District;
use Modules\Geo\Models\Governorate;
use Illuminate\Support\Collection;



class EloquentGeoRepository implements GeoRepositoryinterface
{
    public function __construct(
        private readonly Governorate $governorateModel,
        private readonly City $cityModel,
        private readonly District $districtModel,
    ) {}

    public function getFullTree(): Collection
    {
        return $this->governorateModel
            ->with(['cities.districts'])
            ->orderBy('name_ar')
            ->get();
    }


    public function findGovernorateById(int $id): ?Governorate
    {
        return $this->governorateModel
            ->with(['cities.districts'])
            ->find($id);
    }


    public function findCityById(int $id): ?City
    {
        return $this->cityModel
            ->with('districts')
            ->find($id);
    }

    public function findDistrictById(int $id): ?District
    {
        return $this->districtModel->find($id);
    }

    public function findCityByName(string $name): ?City
    {
        $normalized = trim($name);

        return $this->cityModel
            ->where('name_ar', $normalized)
            ->orWhere('name_en', $normalized)
            ->first();
    }

    public function findDistrictByNameInCity(string $name, int $cityId): ?District
    {
        $normalized = trim($name);

        return $this->districtModel
            ->where('city_id', $cityId)
            ->where(function ($query) use ($normalized) {
                $query->where('name_ar', $normalized)
                    ->orWhere('name_en', $normalized);
            })
            ->first();
    }
}
