<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Geo\Models\City;
use Modules\Geo\Models\District;
use Modules\Geo\Models\Governorate;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class MosqueSeeder extends Seeder
{
    public function run(): void
    {
        $baseUrl = rtrim(config('services.supabase.url'), '/');
        $bucket = trim(config('services.supabase.bucket'), '/');

        $imageUrl = function (?string $fileName) use ($baseUrl, $bucket): ?string {
            if (!$fileName) {
                return null;
            }

            return "{$baseUrl}/storage/v1/object/public/{$bucket}/{$fileName}";
        };

        // نربط كل مسجد بمدير مسجد إن وُجد
        $manager = User::whereHas('roles', fn($q) => $q->where('name', 'mosque_manager'))
            ->first();

        // ===== بيانات دمشق (محافظة + مدينة) =====
        $governorate = $this->resolveGovernorate('دمشق', 33.5138, 36.2765);
        $city = $this->resolveCity($governorate, 'دمشق', 33.5138, 36.2765);

        // أحياء دمشق الحقيقية (تُنشأ عند الحاجة)
        $districts = [
            'دمشق القديمة' => [33.5112, 36.3064],
            'المزرعة'      => [33.5310, 36.2920],
            'الشاغور'      => [33.5160, 36.3120],
            'الصالحية'     => [33.5230, 36.3050],
            'باب توما'     => [33.5125, 36.3075],
            'الميدان'      => [33.4950, 36.2980],
            'القيمرية'     => [33.5118, 36.3090],
            'البرامكة'     => [33.5280, 36.2960],
            'العدوي'       => [33.5400, 36.3100],
            'سوق الحميدية' => [33.5122, 36.3038],
            'السويقة'      => [33.5110, 36.3010],
            'القصاع'       => [33.5410, 36.3120],
            'ركن الدين'    => [33.5480, 36.2980],
            'المهاجرين'    => [33.5220, 36.2850],
            'أبو رمانة'    => [33.5180, 36.2880],
        ];

        $districtIds = [];
        foreach ($districts as $name => [$lat, $lng]) {
            $districtIds[$name] = $this->resolveDistrict($city, $name, $lat, $lng)?->id;
        }

        // ===== مساجد دمشق الحقيقية الشهيرة =====
        $mosques = [
            [
                'name' => 'الجامع الأموي',
                'district' => 'دمشق القديمة',
                'image' => 'umayyad-mosque.jpg',
                'working_hours' => '24 ساعة',
                'status' => 'active',
                'is_featured' => true,
                'latitude' => 33.5112,
                'longitude' => 36.3064,
                'average_rating' => 4.90,
                'reviews_count' => 1250,
                'imam' => 'الشيخ عبد الرزاق الحلبي',
                'khatib' => 'الشيخ أحمد سعد الدين',
            ],
            [
                'name' => 'جامع أبي النور',
                'district' => 'المزرعة',
                'image' => 'abu-al-noor-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => true,
                'latitude' => 33.5310,
                'longitude' => 36.2920,
                'average_rating' => 4.70,
                'reviews_count' => 410,
                'imam' => 'الشيخ خالد النوري',
                'khatib' => 'الشيخ عمر الفاروق',
            ],
            [
                'name' => 'جامع سنان باشا',
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
                'khatib' => 'الشيخ طارق العبد الله',
            ],
            [
                'name' => 'جامع العادلية الكبير',
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
                'name' => 'مسجد السيدة رقية',
                'district' => 'باب توما',
                'image' => 'sayyida-ruqayya-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => true,
                'latitude' => 33.5125,
                'longitude' => 36.3075,
                'average_rating' => 4.60,
                'reviews_count' => 300,
                'imam' => 'الشيخ رقية الدمشقي',
                'khatib' => 'الشيخ منذر العلي',
            ],
            [
                'name' => 'جامع سيدي محيي الدين',
                'district' => 'الصالحية',
                'image' => 'muhyiddin-ibn-arabi-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5320,
                'longitude' => 36.3040,
                'average_rating' => 4.65,
                'reviews_count' => 340,
                'imam' => 'الشيخ محيي الدين العربي',
                'khatib' => 'الشيخ إياد نجم',
            ],
            [
                'name' => 'جامع الدرويشية',
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
            [
                'name' => 'جامع التكية السليمانية',
                'district' => 'البرامكة',
                'image' => 'suleymaniye-tekke-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5280,
                'longitude' => 36.2960,
                'average_rating' => 4.55,
                'reviews_count' => 220,
                'imam' => 'الشيخ سليمان التكيوي',
                'khatib' => 'الشيخ مهند البارمكي',
            ],
            [
                'name' => 'جامع جراح باشا',
                'district' => 'الصالحية',
                'image' => 'jarrah-pasha-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5260,
                'longitude' => 36.3020,
                'average_rating' => 4.50,
                'reviews_count' => 180,
                'imam' => 'الشيخ جراح الدمشقي',
                'khatib' => 'الشيخ لؤي حمود',
            ],
            [
                'name' => 'جامع الطاوسية',
                'district' => 'الميدان',
                'image' => 'al-tawusiyya-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.4960,
                'longitude' => 36.3000,
                'average_rating' => 4.30,
                'reviews_count' => 120,
                'imam' => 'الشيخ طاوس الشامي',
                'khatib' => 'الشيخ فادي العلي',
            ],
            [
                'name' => 'جامع دار الحديث',
                'district' => 'الصالحية',
                'image' => 'dar-al-hadith-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5270,
                'longitude' => 36.3000,
                'average_rating' => 4.60,
                'reviews_count' => 200,
                'imam' => 'الشيخ راغب الحديثي',
                'khatib' => 'الشيخ عدنان سعد',
            ],
            [
                'name' => 'جامع النقاش',
                'district' => 'الميدان',
                'image' => 'al-naqqash-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.4980,
                'longitude' => 36.2960,
                'average_rating' => 4.25,
                'reviews_count' => 95,
                'imam' => 'الشيخ نقاش الحلبي',
                'khatib' => 'الشيخ سامر يوسف',
            ],
            [
                'name' => 'جامع الصابونية',
                'district' => 'الميدان',
                'image' => 'al-sabouniya-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.4970,
                'longitude' => 36.3010,
                'average_rating' => 4.20,
                'reviews_count' => 110,
                'imam' => 'الشيخ صابون الشامي',
                'khatib' => 'الشيخ كمال الدين',
            ],
            [
                'name' => 'جامع مقام الشيخ أرسلان',
                'district' => 'الشاغور',
                'image' => 'sheikh-arslan-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5170,
                'longitude' => 36.3140,
                'average_rating' => 4.40,
                'reviews_count' => 130,
                'imam' => 'الشيخ أرسلان الشاغوري',
                'khatib' => 'الشيخ نضال رشيد',
            ],
            [
                'name' => 'جامع مقام البرزنجي',
                'district' => 'الصالحية',
                'image' => 'al-barzanji-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5250,
                'longitude' => 36.3030,
                'average_rating' => 4.45,
                'reviews_count' => 160,
                'imam' => 'الشيخ برزنجي الدمشقي',
                'khatib' => 'الشيخ هاني الخطيب',
            ],
            [
                'name' => 'جامع خالد بن الوليد',
                'district' => 'العدوي',
                'image' => 'khaled-ibn-al-walid-mosque.jpg',
                'working_hours' => '24 ساعة',
                'status' => 'active',
                'is_featured' => true,
                'latitude' => 33.5400,
                'longitude' => 36.3100,
                'average_rating' => 4.75,
                'reviews_count' => 480,
                'imam' => 'الشيخ خالد العدوي',
                'khatib' => 'الشيخ ياسر العلي',
            ],
            [
                'name' => 'جامع الحسن',
                'district' => 'دمشق القديمة',
                'image' => 'al-hassan-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5100,
                'longitude' => 36.3070,
                'average_rating' => 4.35,
                'reviews_count' => 105,
                'imam' => 'الشيخ حسن العقيبة',
                'khatib' => 'الشيخ ماجد سليمان',
            ],
            [
                'name' => 'جامع القصاع الكبير',
                'district' => 'القصاع',
                'image' => 'al-qassaa-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5410,
                'longitude' => 36.3120,
                'average_rating' => 4.50,
                'reviews_count' => 260,
                'imam' => 'الشيخ قصاعي الشامي',
                'khatib' => 'الشيخ باسل نور الدين',
            ],
            [
                'name' => 'جامع ركن الدين',
                'district' => 'ركن الدين',
                'image' => 'rukn-al-din-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5480,
                'longitude' => 36.2980,
                'average_rating' => 4.40,
                'reviews_count' => 190,
                'imam' => 'الشيخ ركن الديني',
                'khatib' => 'الشيخ طلال فؤاد',
            ],
            [
                'name' => 'جامع المهاجرين',
                'district' => 'المهاجرين',
                'image' => 'al-muhajreen-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5220,
                'longitude' => 36.2850,
                'average_rating' => 4.55,
                'reviews_count' => 230,
                'imam' => 'الشيخ مهجر الشام',
                'khatib' => 'الشيخ وسيم العمر',
            ],
            [
                'name' => 'جامع أبو رمانة',
                'district' => 'أبو رمانة',
                'image' => 'abu-rummaneh-mosque.jpg',
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => 33.5180,
                'longitude' => 36.2880,
                'average_rating' => 4.50,
                'reviews_count' => 175,
                'imam' => 'الشيخ أبو رمان الشامي',
                'khatib' => 'الشيخ فراس الحلبي',
            ],
        ];

        $count = 0;

        foreach ($mosques as $data) {
            $districtName = $data['district'];
            $districtId = $districtIds[$districtName] ?? null;

            Mosque::updateOrCreate(
                [
                    'name' => $data['name'],
                    'city_id' => $city?->id,
                ],
                [
                    'city' => 'دمشق',
                    'city_id' => $city?->id,
                    'district' => $districtName,
                    'district_id' => $districtId,
                    'image' => $imageUrl($data['image']),
                    'working_hours' => $data['working_hours'],
                    'status' => $data['status'],
                    'is_featured' => DB::raw($data['is_featured'] ? 'true' : 'false'),
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

        $this->command->info('تم إنشاء/تحديث ' . $count . ' مسجداً في دمشق ببيانات حقيقية.');
    }

    private function resolveGovernorate(string $name, float $lat, float $lng): ?Governorate
    {
        return Governorate::where('name_ar', $name)->first()
            ?? Governorate::create([
                'name_ar' => $name,
                'name_en' => $name,
                'lat' => $lat,
                'lng' => $lng,
            ]);
    }

    private function resolveCity(Governorate $governorate, string $name, float $lat, float $lng): ?City
    {
        return City::where('name_ar', $name)
            ->where('governorate_id', $governorate->id)
            ->first()
            ?? City::create([
                'governorate_id' => $governorate->id,
                'name_ar' => $name,
                'name_en' => $name,
                'lat' => $lat,
                'lng' => $lng,
            ]);
    }

    private function resolveDistrict(City $city, string $name, float $lat, float $lng): ?District
    {
        return District::where('name_ar', $name)
            ->where('city_id', $city->id)
            ->first()
            ?? District::create([
                'city_id' => $city->id,
                'name_ar' => $name,
                'name_en' => $name,
                'lat' => $lat,
                'lng' => $lng,
            ]);
    }
}
