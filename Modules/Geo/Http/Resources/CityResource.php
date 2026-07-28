<?php

namespace Modules\Geo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'name' => $locale === 'ar' ? $this->name_ar : $this->name_en,
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'districts' => DistrictResources::collection($this->whenLoaded('districts')),
        ];
    }
}
