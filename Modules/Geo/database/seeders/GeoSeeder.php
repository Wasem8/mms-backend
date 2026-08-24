<?php

declare(strict_types=1);

namespace Modules\Geo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Geo\Models\City;
use Modules\Geo\Models\District;
use Modules\Geo\Models\Governorate;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->data() as $govData) {
                $governorate = Governorate::create([
                    'name_ar' => $govData['name_ar'],
                    'name_en' => $govData['name_en'],
                    'lat' => $govData['lat'],
                    'lng' => $govData['lng'],
                ]);

                foreach ($govData['cities'] as $cityData) {
                    City::create([
                        'governorate_id' => $governorate->id,
                        'name_ar' => $cityData['name_ar'],
                        'name_en' => $cityData['name_en'],
                        'lat' => $cityData['lat'],
                        'lng' => $cityData['lng'],
                    ]);
                    // districts تبدأ فاضية عن قصد - يتعبّوا لاحقًا
                    // إما يدويًا أو عبر الـ backfill command
                }
            }
        });
    }

    /**
     * @return array<int, array{name_ar: string, name_en: string, lat: float, lng: float, cities: array}>
     */
    private function data(): array
    {
        return [
            [
                'name_ar' => 'دمشق',
                'name_en' => 'Damascus',
                'lat' => 33.5138,
                'lng' => 36.2765,
                'cities' => [],
            ],
            [
                'name_ar' => 'ريف دمشق',
                'name_en' => 'Rif Dimashq',
                'lat' => 33.5722,
                'lng' => 36.4022, // مركزها = دوما (حسب اقتراح المستند)
                'cities' => [
                    ['name_ar' => 'التل', 'name_en' => 'Al-Tall', 'lat' => 33.6103, 'lng' => 36.3103],
                    ['name_ar' => 'دوما', 'name_en' => 'Douma', 'lat' => 33.5722, 'lng' => 36.4022],
                    ['name_ar' => 'داريا', 'name_en' => 'Daraya', 'lat' => 33.4583, 'lng' => 36.2367],
                    ['name_ar' => 'جرمانا', 'name_en' => 'Jaramana', 'lat' => 33.4869, 'lng' => 36.3450],
                    ['name_ar' => 'حرستا', 'name_en' => 'Harasta', 'lat' => 33.5603, 'lng' => 36.3614],
                    ['name_ar' => 'عربين', 'name_en' => 'Arbin', 'lat' => 33.5419, 'lng' => 36.3589],
                    ['name_ar' => 'قدسيا', 'name_en' => 'Qudsaya', 'lat' => 33.5419, 'lng' => 36.2236],
                    ['name_ar' => 'معضمية الشام', 'name_en' => 'Moadamiyeh', 'lat' => 33.4639, 'lng' => 36.1933],
                    ['name_ar' => 'صحنايا', 'name_en' => 'Sahnaya', 'lat' => 33.4106, 'lng' => 36.2222],
                    ['name_ar' => 'الكسوة', 'name_en' => 'Al-Kiswah', 'lat' => 33.3578, 'lng' => 36.2386],
                    ['name_ar' => 'قطنا', 'name_en' => 'Qatana', 'lat' => 33.4378, 'lng' => 36.0775],
                    ['name_ar' => 'الزبداني', 'name_en' => 'Zabadani', 'lat' => 33.7250, 'lng' => 36.1006],
                    ['name_ar' => 'يبرود', 'name_en' => 'Yabroud', 'lat' => 33.9683, 'lng' => 36.6575],
                    ['name_ar' => 'النبك', 'name_en' => 'Al-Nabek', 'lat' => 34.0247, 'lng' => 36.7269],
                ],
            ],
            [
                'name_ar' => 'حلب',
                'name_en' => 'Aleppo',
                'lat' => 36.2021,
                'lng' => 37.1343,
                'cities' => [
                    ['name_ar' => 'منبج', 'name_en' => 'Manbij', 'lat' => 36.5281, 'lng' => 37.9549],
                    ['name_ar' => 'الباب', 'name_en' => 'Al-Bab', 'lat' => 36.3700, 'lng' => 37.5158],
                    ['name_ar' => 'اعزاز', 'name_en' => 'Azaz', 'lat' => 36.5867, 'lng' => 37.0469],
                    ['name_ar' => 'عفرين', 'name_en' => 'Afrin', 'lat' => 36.5119, 'lng' => 36.8694],
                    ['name_ar' => 'جرابلس', 'name_en' => 'Jarabulus', 'lat' => 36.8175, 'lng' => 38.0117],
                    ['name_ar' => 'السفيرة', 'name_en' => 'Al-Safira', 'lat' => 36.0772, 'lng' => 37.3739],
                    ['name_ar' => 'عين العرب (كوباني)', 'name_en' => 'Ayn al-Arab (Kobani)', 'lat' => 36.8911, 'lng' => 38.3539],
                ],
            ],
            [
                'name_ar' => 'حمص',
                'name_en' => 'Homs',
                'lat' => 34.7308,
                'lng' => 36.7090,
                'cities' => [
                    ['name_ar' => 'الرستن', 'name_en' => 'Al-Rastan', 'lat' => 34.9258, 'lng' => 36.7325],
                    ['name_ar' => 'تلبيسة', 'name_en' => 'Talbiseh', 'lat' => 34.8389, 'lng' => 36.7311],
                    ['name_ar' => 'القصير', 'name_en' => 'Al-Qusayr', 'lat' => 34.5083, 'lng' => 36.5797],
                    ['name_ar' => 'تدمر', 'name_en' => 'Palmyra', 'lat' => 34.5559, 'lng' => 38.2840],
                    ['name_ar' => 'القريتين', 'name_en' => 'Al-Qaryatayn', 'lat' => 34.2294, 'lng' => 37.2406],
                ],
            ],
            [
                'name_ar' => 'حماة',
                'name_en' => 'Hama',
                'lat' => 35.1318,
                'lng' => 36.7578,
                'cities' => [
                    ['name_ar' => 'سلمية', 'name_en' => 'Salamiyah', 'lat' => 35.0114, 'lng' => 37.0531],
                    ['name_ar' => 'مصياف', 'name_en' => 'Masyaf', 'lat' => 35.0653, 'lng' => 36.3403],
                    ['name_ar' => 'محردة', 'name_en' => 'Mhardeh', 'lat' => 35.2458, 'lng' => 36.5708],
                    ['name_ar' => 'السقيلبية', 'name_en' => 'Al-Suqaylabiyah', 'lat' => 35.3672, 'lng' => 36.3936],
                ],
            ],
            [
                'name_ar' => 'اللاذقية',
                'name_en' => 'Latakia',
                'lat' => 35.5196,
                'lng' => 35.7915,
                'cities' => [
                    ['name_ar' => 'جبلة', 'name_en' => 'Jableh', 'lat' => 35.3619, 'lng' => 35.9214],
                    ['name_ar' => 'القرداحة', 'name_en' => 'Qardaha', 'lat' => 35.4581, 'lng' => 36.0589],
                    ['name_ar' => 'الحفة', 'name_en' => 'Al-Haffah', 'lat' => 35.6017, 'lng' => 36.0339],
                ],
            ],
            [
                'name_ar' => 'طرطوس',
                'name_en' => 'Tartus',
                'lat' => 34.8890,
                'lng' => 35.8866,
                'cities' => [
                    ['name_ar' => 'بانياس', 'name_en' => 'Baniyas', 'lat' => 35.1822, 'lng' => 35.9486],
                    ['name_ar' => 'صافيتا', 'name_en' => 'Safita', 'lat' => 34.8197, 'lng' => 36.1175],
                    ['name_ar' => 'الدريكيش', 'name_en' => 'Duraykish', 'lat' => 34.8919, 'lng' => 36.1383],
                ],
            ],
            [
                'name_ar' => 'إدلب',
                'name_en' => 'Idlib',
                'lat' => 35.9306,
                'lng' => 36.6339,
                'cities' => [
                    ['name_ar' => 'معرة النعمان', 'name_en' => 'Maarat al-Numan', 'lat' => 35.6480, 'lng' => 36.6786],
                    ['name_ar' => 'أريحا', 'name_en' => 'Ariha', 'lat' => 35.8108, 'lng' => 36.6072],
                    ['name_ar' => 'جسر الشغور', 'name_en' => 'Jisr al-Shughur', 'lat' => 35.8144, 'lng' => 36.3208],
                    ['name_ar' => 'سراقب', 'name_en' => 'Saraqib', 'lat' => 35.8639, 'lng' => 36.8056],
                    ['name_ar' => 'خان شيخون', 'name_en' => 'Khan Shaykhun', 'lat' => 35.4419, 'lng' => 36.6506],
                ],
            ],
            [
                'name_ar' => 'درعا',
                'name_en' => 'Daraa',
                'lat' => 32.6189,
                'lng' => 36.1021,
                'cities' => [
                    ['name_ar' => 'نوى', 'name_en' => 'Nawa', 'lat' => 32.8889, 'lng' => 36.0431],
                    ['name_ar' => 'إزرع', 'name_en' => 'Izra', 'lat' => 32.8703, 'lng' => 36.2553],
                    ['name_ar' => 'الصنمين', 'name_en' => 'Al-Sanamayn', 'lat' => 33.0717, 'lng' => 36.1808],
                    ['name_ar' => 'بصرى الشام', 'name_en' => 'Bosra', 'lat' => 32.5181, 'lng' => 36.4819],
                ],
            ],
            [
                'name_ar' => 'السويداء',
                'name_en' => 'As-Suwayda',
                'lat' => 32.7094,
                'lng' => 36.5694,
                'cities' => [
                    ['name_ar' => 'شهبا', 'name_en' => 'Shahba', 'lat' => 32.8542, 'lng' => 36.6275],
                    ['name_ar' => 'صلخد', 'name_en' => 'Salkhad', 'lat' => 32.4922, 'lng' => 36.7128],
                ],
            ],
            [
                'name_ar' => 'القنيطرة',
                'name_en' => 'Quneitra',
                'lat' => 33.1264,
                'lng' => 35.8245,
                'cities' => [
                    ['name_ar' => 'خان أرنبة', 'name_en' => 'Khan Arnabah', 'lat' => 33.1747, 'lng' => 35.9089],
                ],
            ],
            [
                'name_ar' => 'دير الزور',
                'name_en' => 'Deir ez-Zor',
                'lat' => 35.3359,
                'lng' => 40.1408,
                'cities' => [
                    ['name_ar' => 'الميادين', 'name_en' => 'Al-Mayadin', 'lat' => 35.0186, 'lng' => 40.4506],
                    ['name_ar' => 'البوكمال', 'name_en' => 'Albu Kamal', 'lat' => 34.4531, 'lng' => 40.9189],
                ],
            ],
            [
                'name_ar' => 'الرقة',
                'name_en' => 'Raqqa',
                'lat' => 35.9594,
                'lng' => 39.0078,
                'cities' => [
                    ['name_ar' => 'الطبقة', 'name_en' => 'Al-Tabqa', 'lat' => 35.8369, 'lng' => 38.5483],
                    ['name_ar' => 'تل أبيض', 'name_en' => 'Tell Abyad', 'lat' => 36.6967, 'lng' => 38.9536],
                ],
            ],
            [
                'name_ar' => 'الحسكة',
                'name_en' => 'Al-Hasakah',
                'lat' => 36.5024,
                'lng' => 40.7477,
                'cities' => [
                    ['name_ar' => 'القامشلي', 'name_en' => 'Qamishli', 'lat' => 37.0521, 'lng' => 41.2314],
                    ['name_ar' => 'رأس العين', 'name_en' => 'Ras al-Ayn', 'lat' => 36.8506, 'lng' => 40.0706],
                    ['name_ar' => 'عامودا', 'name_en' => 'Amuda', 'lat' => 37.1042, 'lng' => 40.9314],
                    ['name_ar' => 'المالكية', 'name_en' => 'Al-Malikiyah', 'lat' => 37.1764, 'lng' => 42.1383],
                ],
            ],
        ];
    }
}
