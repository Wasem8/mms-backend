<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque;

class MosqueSeeder extends Seeder
{
    public function run(): void
    {
        $baseUrl = rtrim(config('services.supabase.url'), '/');
        $bucket = trim(config('services.supabase.bucket'), '/');

        $imageUrl = function (string $fileName) use ($baseUrl, $bucket): string {
            return "{$baseUrl}/storage/v1/object/public/{$bucket}/{$fileName}";
        };

        $mosques = [

            [
                'name' => 'الجامع الأموي',
                'city' => 'دمشق',
                'district' => 'دمشق القديمة',
                'image' => $imageUrl('umayyad-mosque.jpg'),
                'working_hours' => '24 ساعة',
                'status' => 'active',
                'latitude' => 33.5112,
                'longitude' => 36.3064,
                'average_rating' => 4.90,
                'reviews_count' => 1250,
                'imam' => 'إمام الجامع الأموي',
                'khatib' => 'خطيب الجامع الأموي',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع الحسن',
                'city' => 'دمشق',
                'district' => 'المزة',
                'image' => $imageUrl('al-hassan-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'latitude' => 33.4975,
                'longitude' => 36.2585,
                'average_rating' => 4.70,
                'reviews_count' => 340,
                'imam' => 'إمام جامع الحسن',
                'khatib' => 'خطيب جامع الحسن',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع الرحمن',
                'city' => 'دمشق',
                'district' => 'كفرسوسة',
                'image' => $imageUrl('al-rahman-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'latitude' => 33.4805,
                'longitude' => 36.2570,
                'average_rating' => 4.60,
                'reviews_count' => 280,
                'imam' => 'إمام جامع الرحمن',
                'khatib' => 'خطيب جامع الرحمن',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع الإيمان',
                'city' => 'دمشق',
                'district' => 'المزرعة',
                'image' => $imageUrl('al-iman-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',
                'latitude' => 33.5200,
                'longitude' => 36.2870,
                'average_rating' => 4.50,
                'reviews_count' => 190,
                'imam' => 'إمام جامع الإيمان',
                'khatib' => 'خطيب جامع الإيمان',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع عثمان بن عفان',
                'city' => 'دمشق',
                'district' => 'برزة',
                'image' => $imageUrl('uthman-ibn-affan-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',

                'latitude' => 33.5520,
                'longitude' => 36.3220,
                'average_rating' => 4.40,
                'reviews_count' => 150,
                'imam' => 'إمام جامع عثمان بن عفان',
                'khatib' => 'خطيب جامع عثمان بن عفان',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع التوبة',
                'city' => 'دمشق',
                'district' => 'ركن الدين',
                'image' => $imageUrl('al-tawba-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',

                'latitude' => 33.5350,
                'longitude' => 36.2930,
                'average_rating' => 4.30,
                'reviews_count' => 120,
                'imam' => 'إمام جامع التوبة',
                'khatib' => 'خطيب جامع التوبة',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع النور',
                'city' => 'دمشق',
                'district' => 'العدوي',
                'image' => $imageUrl('al-noor-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',

                'latitude' => 33.5300,
                'longitude' => 36.3100,
                'average_rating' => 4.20,
                'reviews_count' => 95,
                'imam' => 'إمام جامع النور',
                'khatib' => 'خطيب جامع النور',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع الرحمة',
                'city' => 'دمشق',
                'district' => 'الميدان',
                'image' => $imageUrl('al-rahma-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',

                'latitude' => 33.4850,
                'longitude' => 36.2940,
                'average_rating' => 4.40,
                'reviews_count' => 175,
                'imam' => 'إمام جامع الرحمة',
                'khatib' => 'خطيب جامع الرحمة',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع خالد بن الوليد',
                'city' => 'دمشق',
                'district' => 'القصاع',
                'image' => $imageUrl('khalid-ibn-al-walid-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',

                'latitude' => 33.5200,
                'longitude' => 36.3150,
                'average_rating' => 4.30,
                'reviews_count' => 130,
                'imam' => 'إمام جامع خالد بن الوليد',
                'khatib' => 'خطيب جامع خالد بن الوليد',
                'manager_id' => null,
            ],

            [
                'name' => 'جامع التقوى',
                'city' => 'دمشق',
                'district' => 'دمر',
                'image' => $imageUrl('al-taqwa-mosque.jpg'),
                'working_hours' => '05:00 - 22:00',
                'status' => 'active',

                'latitude' => 33.5350,
                'longitude' => 36.2300,
                'average_rating' => 4.10,
                'reviews_count' => 85,
                'imam' => 'إمام جامع التقوى',
                'khatib' => 'خطيب جامع التقوى',
                'manager_id' => null,
            ],
        ];

        foreach ($mosques as $data) {

            Mosque::updateOrCreate(
                [
                    'name' => $data['name'],
                    'city' => $data['city'],
                ],
                $data
            );
        }

        $this->command->info(
            '✅ تم إنشاء/تحديث ' . count($mosques) . ' مساجد في دمشق.'
        );
    }
}
