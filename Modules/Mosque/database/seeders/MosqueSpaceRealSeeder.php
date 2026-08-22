<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;

class MosqueSpaceRealSeeder extends Seeder
{
    public function run(): void
    {
        // قوالب المساحات لكل مسجد (بالعربية)
        $templates = [
            ['المصلى الرئيسي', 400],
            ['مصلى النساء', 150],
            ['قاعة تحفيظ القرآن', 60],
            ['قاعة المناسبات', 200],
        ];

        $mosques = Mosque::all();
        $count = 0;

        foreach ($mosques as $mosque) {
            foreach ($templates as [$name, $capacity]) {
                MosqueSpace::updateOrCreate(
                    [
                        'mosque_id' => $mosque->id,
                        'name' => $name,
                    ],
                    [
                        'capacity' => $capacity,
                    ]
                );

                $count++;
            }
        }

        $this->command->info('تم إنشاء/تحديث ' . $count . ' مساحة في مساجد دمشق.');
    }
}
