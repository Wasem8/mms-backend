<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class MosqueSeeder extends Seeder
{
    public function run(): void
    {
        $mosquesData = [
            [
                'name' => 'مسجد الرحمن',
                'image' => 'mosques/mosque1.png',
                'working_hours' => '5:00 AM - 10:00 PM',
                'status' => 'active',
                'is_featured' => true,
                'city' => 'دمشق',
                'district' => 'المزة',
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'average_rating' => 4.0,
                'reviews_count' => 0,
                'imam' => 'الشيخ أحمد',
                'khatib' => 'الشيخ عبد الله',
            ],
            [
                'name' => 'مسجد النور',
                'image' => 'mosques/mosque2.png',
                'working_hours' => '4:30 AM - 11:00 PM',
                'status' => 'active',
                'is_featured' => false,
                'city' => 'حلب',
                'district' => 'السليمانية',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'average_rating' => 3.5,
                'reviews_count' => 1,
                'imam' => 'الشيخ محمد',
                'khatib' => 'الشيخ يوسف',
            ],
            [
                'name' => 'مسجد الإيمان',
                'image' => 'mosques/mosque3.png',
                'working_hours' => '5:00 AM - 9:30 PM',
                'status' => 'active',
                'is_featured' => true,
                'city' => 'حمص',
                'district' => 'باب السباع',
                'latitude' => 34.7324,
                'longitude' => 36.7182,
                'average_rating' => 5.0,
                'reviews_count' => 2,
                'imam' => 'الشيخ عمر',
                'khatib' => 'الشيخ خالد',
            ],
            [
                'name' => 'مسجد التقوى',
                'image' => 'mosques/mosque2.png',
                'working_hours' => '5:15 AM - 10:15 PM',
                'status' => 'active',
                'is_featured' => false,
                'city' => 'اللاذقية',
                'district' => 'الصليبة',
                'latitude' => 35.5317,
                'longitude' => 35.7901,
                'average_rating' => 0,
                'reviews_count' => 0,
                'imam' => 'الشيخ حسن',
                'khatib' => 'الشيخ محمود',
            ],
            [
                'name' => 'مسجد الرحمن',
                'image' => 'mosques/mosque1.png',
                'working_hours' => '5:00 AM - 10:00 PM',
                'status' => 'active',
                'is_featured' => true,
                'city' => 'دمشق',
                'district' => 'المزة',
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'average_rating' => 4.0,
                'reviews_count' => 0,
                'imam' => 'الشيخ أحمد',
                'khatib' => 'الشيخ عبد الله',
            ],
            [
                'name' => 'مسجد النور',
                'image' => 'mosques/mosque2.png',
                'working_hours' => '4:30 AM - 11:00 PM',
                'status' => 'active',
                'is_featured' => false,
                'city' => 'حلب',
                'district' => 'السليمانية',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'average_rating' => 3.5,
                'reviews_count' => 1,
                'imam' => 'الشيخ محمد',
                'khatib' => 'الشيخ يوسف',
            ],
            [
                'name' => 'مسجد الإيمان',
                'image' => 'mosques/mosque3.png',
                'working_hours' => '5:00 AM - 9:30 PM',
                'status' => 'active',
                'is_featured' => true,
                'city' => 'حمص',
                'district' => 'باب السباع',
                'latitude' => 34.7324,
                'longitude' => 36.7182,
                'average_rating' => 5.0,
                'reviews_count' => 2,
                'imam' => 'الشيخ عمر',
                'khatib' => 'الشيخ خالد',
            ],
            [
                'name' => 'مسجد التقوى',
                'image' => 'mosques/mosque2.png',
                'working_hours' => '5:15 AM - 10:15 PM',
                'status' => 'active',
                'is_featured' => false,
                'city' => 'اللاذقية',
                'district' => 'الصليبة',
                'latitude' => 35.5317,
                'longitude' => 35.7901,
                'average_rating' => 0,
                'reviews_count' => 0,
                'imam' => 'الشيخ حسن',
                'khatib' => 'الشيخ محمود',
            ],
        ];

        foreach ($mosquesData as $index => $data) {
            $number = $index + 1;

            // إنشاء مستخدم manager مستقل لكل مسجد
            $manager = User::firstOrCreate(
                ['email' => "mosque.manager{$number}@wasl.test"],
                [
                    'name' => "مدير {$data['name']}",
                    'first_name' => 'مدير',
                    'last_name' => (string) $number,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $manager->assignRole('mosque_manager');

            Mosque::create($data + ['manager_id' => $manager->id]);
        }
    }
}
