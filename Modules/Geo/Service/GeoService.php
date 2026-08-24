<?php

namespace Modules\Geo\Service;

use Illuminate\Support\Facades\Cache;
use Modules\Geo\Http\Resources\GovernorateResource;
use Modules\Geo\Repositories\GeoRepositoryInterface;

class GeoService
{
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        private readonly GeoRepositoryInterface $repository,
    ) {}

    public function getCatalog(): array
    {
        $locale = app()->getLocale();
        $cacheKey = "geo:catalog:tree:{$locale}";

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            function () {
                $resource = GovernorateResource::collection($this->repository->getFullTree());

                // يجبر التحويل الكامل والمتداخل لـ array نظيف
                // بدون أي كائنات Resource متبقية جوا الهيكل
                return json_decode(json_encode($resource), true);
            },
        );
    }

    public function flushCache(): void
    {
        Cache::forget('geo:catalog:tree:ar');
        Cache::forget('geo:catalog:tree:en');
    }
}
