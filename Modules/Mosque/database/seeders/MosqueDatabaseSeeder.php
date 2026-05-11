<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque; // تأكد من مسار الموديل الصحيح في موديولك

class MosqueDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mosques = [
            [
                'name' => 'جامع الراجحي الكبير',
                'image' => 'https://example.com/images/rajhi.jpg',
                'working_hours' => 'مفتوح 24 ساعة',
                'status' => 'active',
                'is_featured' => true,
                'city' => 'الرياض',
                'district' => 'حي الجزيرة',
                'latitude' => 24.69372200,
                'longitude' => 46.72355600,
                'imam' => 'الشيخ ناصر القطامي',
                'khatib' => 'الشيخ صالح المغامسي',
                'manager_id' => null, // اتركها null أو ضع ID مستخدم موجود
            ],
            [
                'name' => 'مسجد قباء',
                'image' => 'https://example.com/images/quba.jpg',
                'working_hours' => 'مفتوح من الفجر حتى العشاء',
                'status' => 'active',
                'is_featured' => true,
                'city' => 'المدينة المنورة',
                'district' => 'حي قباء',
                'latitude' => 24.43920000,
                'longitude' => 39.61720000,
                'imam' => 'الشيخ عماد حافظ',
                'khatib' => 'الشيخ محمد عابد',
                'manager_id' => null,
            ],
            [
                'name' => 'جامع الملك فهد',
                'image' => null,
                'working_hours' => 'مفتوح لأوقات الصلوات',
                'status' => 'maintenance',
                'is_featured' => false,
                'city' => 'جدة',
                'district' => 'حي الشاطئ',
                'latitude' => 21.54330000,
                'longitude' => 39.17280000,
                'imam' => 'الشيخ أحمد الحذيفي',
                'khatib' => 'الشيخ علي جابر',
                'manager_id' => null,
            ]
        ];

        foreach ($mosques as $mosqueData) {
            Mosque::create($mosqueData);
        }
    }
}
