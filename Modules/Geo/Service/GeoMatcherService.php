<?php

declare(strict_types=1);

namespace Modules\Geo\Service;

use Modules\Geo\Models\City;
use Modules\Geo\Repositories\GeoRepositoryInterface;

class GeoMatcherService
{
    public function __construct(
        private readonly GeoRepositoryInterface $repository,
    ) {}

    /**
     * @return array{city_id: int, district_id: int|null}|null
     *         null = ما قدرنا نطابق حتى المدينة (يحتاج مراجعة يدوية)
     */
    public function matchCityAndDistrict($cityName, $districtName)
    {
        // 1. البحث عن المدينة في كلا العمودين (العربي أو الإنجليزي)
        $city = City::where('name_ar', $cityName)
            ->orWhere('name_en', $cityName)
            ->first();

        if (! $city) {
            return false; // المدينة غير موجودة في الفهرس
        }

        $districtId = null;

        // 2. إذا كان المسجد يحتوي على حي، ابحث عنه أو قم بإنشائه فوراً
        if (!empty($districtName)) {
            // نستخدم name_ar للبحث والإنشاء لأن البيانات القديمة غالباً باللغة العربية
            $district = $city->districts()->firstOrCreate(
                ['name_ar' => $districtName], // شروط البحث
                ['name_en' => $districtName]  // القيم الإضافية عند الإنشاء (يمكنك تركها null أو كما تفضل)
            );

            $districtId = $district->id;
        }

        return [
            'city_id' => $city->id,
            'district_id' => $districtId,
        ];
    }
    /**
     * تنظيف بسيط قبل المطابقة: يشيل مسافات زايدة بالبداية/النهاية
     * ويوحّد المسافات المتعددة لمسافة وحدة.
     */
    private function normalize(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value));
    }
}
