<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Geo\Models\City;
use Modules\Geo\Models\Governorate;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class MosqueSeeder extends Seeder
{
    public function run(): void
    {
        $baseUrl = rtrim(config('services.supabase.url'), '/');
        $bucket = trim(config('services.supabase.bucket'), '/');

        $imageUrl = function (string $fileName) use ($baseUrl, $bucket): string {
            return "{$baseUrl}/storage/v1/object/public/{$bucket}/{$fileName}";
        };

        // يعتمد على بيانات المستخدمين: نربط كل مسجد بمدير مسجد إن وُجد
        $manager = User::whereHas('roles', fn($q) => $q->where('name', 'mosque_manager'))
            ->first();

        $mosques = [
            // ===== دمشق =====
            [
                'name' => 'الجامع الأموي',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'دمشق القديمة',
                'image' => 'umayyad-mosque.jpg',
                'working_hours' => '24 ساعة',
                'status' => 'active',
                'is_featured' => true,
                'latitude' => 33.5112,
                'longitude' => 36.3064,
                'average_rating' => 4.90,
                'reviews_count' => 1250,
                'imam' => 'الشيخ محمد الأموي',
                'khatib' => 'الشيخ أحمد سعد',
            ],
            [
                'name' => 'جامع أبي النور',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'المزرعة',
                'image' => 'abu-al-noor-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5310,
                'longitude' => 36.2920,
                'average_rating' => 4.70,
                'reviews_count' => 410,
                'imam' => 'الشيخ خالد النوري',
                'khatib' => 'الشيخ عمر الفاروق',
            ],
            [
                'name' => 'جامع العادلية الكبير',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'السويقة',
                'image' => 'al-adiliyah-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5110,
                'longitude' => 36.3010,
                'average_rating' => 4.60,
                'reviews_count' => 320,
                'imam' => 'الشيخ يوسف العادلي',
                'khatib' => 'الشيخ سمير حسن',
            ],
            [
                'name' => 'جامع سنان باشا',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'الشاغور',
                'image' => 'sinan-pasha-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5160,
                'longitude' => 36.3120,
                'average_rating' => 4.50,
                'reviews_count' => 210,
                'imam' => 'الشيخ زيد السناني',
                'khatib' => 'الشيخ طارق العبد',
            ],
            [
                'name' => 'جامع تنكز',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'الصالحية',
                'image' => 'tankiz-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5230,
                'longitude' => 36.3050,
                'average_rating' => 4.40,
                'reviews_count' => 160,
                'imam' => 'الشيخ وليد التنكزي',
                'khatib' => 'الشيخ فادي رمضان',
            ],
            [
                'name' => 'جامع السيدة رقية',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'باب توما',
                'image' => 'sayyida-ruqayya-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5125,
                'longitude' => 36.3075,
                'average_rating' => 4.60,
                'reviews_count' => 300,
                'imam' => 'الشيخ رقية الدمشقي',
                'khatib' => 'الشيخ منذر العلي',
            ],
            [
                'name' => 'جامع سيدي محيي الدين',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'الصالحية',
                'image' => 'muhyiddin-ibn-arabi-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => true,
                'latitude' => 33.5320,
                'longitude' => 36.3040,
                'average_rating' => 4.65,
                'reviews_count' => 340,
                'imam' => 'الشيخ محيي الدين العربي',
                'khatib' => 'الشيخ إياد نجم',
            ],
            [
                'name' => 'جامع الدرويشية',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'سوق الحميدية',
                'image' => 'al-darwishiyya-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5122,
                'longitude' => 36.3038,
                'average_rating' => 4.45,
                'reviews_count' => 175,
                'imam' => 'الشيخ درويش حلاق',
                'khatib' => 'الشيخ بلال زين',
            ],
            [
                'name' => 'جامع السنجقدار',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'الميدان',
                'image' => 'al-sinjaqdar-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.4950,
                'longitude' => 36.2980,
                'average_rating' => 4.35,
                'reviews_count' => 140,
                'imam' => 'الشيخ سنجق داري',
                'khatib' => 'الشيخ أنس فارس',
            ],
            [
                'name' => 'جامع القيمرية',
                'governorate' => 'دمشق',
                'city' => 'دمشق',
                'city_lat' => 33.5138,
                'city_lng' => 36.2765,
                'district' => 'القيمرية',
                'image' => 'al-qaymariyya-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5118,
                'longitude' => 36.3090,
                'average_rating' => 4.40,
                'reviews_count' => 150,
                'imam' => 'الشيخ قيمري الشامي',
                'khatib' => 'الشيخ حسام الجندي',
            ],
        ];

        // جميع المساجد في مدينة واحدة: دمشق
        $city = $this->resolveCity('دمشق', 'دمشق', 33.5138, 36.2765);

        // الاقتصار على مساجد دمشق فقط (10 مساجد) - فلترة صريحة بدل الاعتماد على ترتيب المصفوفة
        $mosques = array_values(array_filter($mosques, fn($m) => $m['city'] === 'دمشق'));
        $mosques = array_slice($mosques, 0, 10);

        $count = 0;

        foreach ($mosques as $data) {

            Mosque::updateOrCreate(
                [
                    'name' => $data['name'],
                    'city_id' => $city?->id,
                ],
                [
                    'city_id' => $city?->id,
                    'district_id' => null,
                    'district' => $data['district'],
                    'image' => $imageUrl($data['image']),
                    'working_hours' => $data['working_hours'],
                    'status' => $data['status'],
                    'is_featured' => $data['is_featured'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'average_rating' => $data['average_rating'],
                    'reviews_count' => $data['reviews_count'],
                    'imam' => $data['imam'],
                    'khatib' => $data['khatib'],
                    'manager_id' => $manager?->id,
                ]
            );

            $count++;
        }

        $this->command->info('تم إنشاء/تحديث ' . $count . ' مسجداً في دمشق.');
    }

    /**
     * يجلب المدينة حسب الاسم، وينشئ المحافظة/المدينة عند الحاجة كي لا تفشل المفاتيح الأجنبية.
     */
    private function resolveCity(string $governorateName, string $cityName, float $lat, float $lng): ?City
    {
        $governorate = Governorate::where('name_ar', $governorateName)->first()
            ?? Governorate::create([
                'name_ar' => $governorateName,
                'name_en' => $governorateName,
                'lat' => $lat,
                'lng' => $lng,
            ]);

        return City::where('name_ar', $cityName)
            ->where('governorate_id', $governorate->id)
            ->first()
            ?? City::create([
                'governorate_id' => $governorate->id,
                'name_ar' => $cityName,
                'name_en' => $cityName,
                'lat' => $lat,
                'lng' => $lng,
            ]);
    }
}
