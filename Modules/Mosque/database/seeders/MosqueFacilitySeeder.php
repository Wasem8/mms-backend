<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Facility;
use Modules\Mosque\Models\Mosque;

class MosqueFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilityNames = [
            'مصلى النساء',
            'موضأ',
            'مكتبة',
            'دار تحفيظ القرآن',
            'قاعة مناسبات',
            'مصعد',
            'مكيفات',
            'مواقف سيارات',
            'مئذنة',
            'مدرسة دينية',
        ];

        $facilities = collect($facilityNames)
            ->map(fn(string $name) => Facility::firstOrCreate(['name' => $name]));

        $mosques = Mosque::all();

        foreach ($mosques as $mosque) {
            $chosen = $facilities
                ->whereIn('name', $this->facilitiesFor($mosque))
                ->pluck('id')
                ->all();

            $mosque->facilities()->syncWithoutDetaching($chosen);
        }

        $this->command->info('تم ربط المرافق بـ ' . $mosques->count() . ' مسجداً في دمشق.');
    }

    private function facilitiesFor(Mosque $mosque): array
    {
        // كل مسجد يملك هذه المرافق الأساسية
        $base = ['موضأ', 'مئذنة', 'مصلى النساء', 'مكيفات', 'مواقف سيارات'];

        // المساجد المميزة تحصل على مرافق إضافية
        if ($mosque->is_featured) {
            $base = array_merge($base, [
                'مكتبة',
                'دار تحفيظ القرآن',
                'قاعة مناسبات',
                'مصعد',
                'مدرسة دينية',
            ]);
        }

        return array_values(array_unique($base));
    }
}
