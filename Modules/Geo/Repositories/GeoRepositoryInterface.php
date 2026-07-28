<?php

namespace Modules\Geo\Repositories;

use Illuminate\Support\Collection;
use Modules\Geo\Models\City;
use Modules\Geo\Models\District;
use Modules\Geo\Models\Governorate;

interface GeoRepositoryInterface
{
    public function getFullTree(): Collection;

    public function findGovernorateById(int $id): ?Governorate;

    public function findCityById(int $id): ?City;

    public function findDistrictById(int $id): ?District;

    public function findCityByName(string $name): ?City;

    public function findDistrictByNameInCity(string $name, int $cityId): ?District;
}
