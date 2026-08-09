<?php

namespace Modules\Geo\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Geo\Service\GeoMatcherService as ServiceGeoMatcherService;
use Modules\Geo\Services\GeoMatcherService;
use Modules\Mosque\Models\Mosque;

class BackfillMosqueGeoCommand extends Command
{

protected $signature = 'geo:backfill-mosques';


protected $description = 'Backfills city_id and district_id on mosques using legacy text fields.';

public function __construct()
{
parent::__construct();
}

public function handle(ServiceGeoMatcherService $matcher): void
{
$this->info('Starting mosque geo backfill...');

Mosque::whereNull('city_id')->chunk(100, function ($mosques) use ($matcher) {
foreach ($mosques as $mosque) {
$match = $matcher->matchCityAndDistrict($mosque->city, $mosque->district);
if ($match) {
$mosque->update([
'city_id' => $match['city_id'],
'district_id' => $match['district_id'],
]);
} else {
Log::warning("Unmatched mosque geo: {$mosque->id} - {$mosque->city}/{$mosque->district}");
}
}
});

$this->info('Backfill completed! Check logs for unmatched records.');
}

}
