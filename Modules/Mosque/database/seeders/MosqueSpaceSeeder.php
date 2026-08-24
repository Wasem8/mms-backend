<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;

class MosqueSpaceSeeder extends Seeder
{
    /**
     * المساحات الأساسية التي يمكن أن توجد في المسجد.
     */
    private array $spaces = [
        [
            'name' => 'المصلى الرئيسي',
            'capacity' => 500,
        ],
        [
            'name' => 'مصلى النساء',
            'capacity' => 200,
        ],
        [
            'name' => 'قاعة تحفيظ القرآن',
            'capacity' => 40,
        ],
        [
            'name' => 'قاعة المحاضرات',
            'capacity' => 100,
        ],
        [
            'name' => 'غرفة المعلمين',
            'capacity' => 15,
        ],
        [
            'name' => 'مكتبة المسجد',
            'capacity' => 30,
        ],
        [
            'name' => 'قاعة الأنشطة',
            'capacity' => 60,
        ],
        [
            'name' => 'الساحة الخارجية',
            'capacity' => 300,
        ],
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🔄 بدء إنشاء مساحات المساجد...');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        /*
         * جلب المساجد الموجودة مسبقاً.
         */
        $mosques = Mosque::query()
            ->orderBy('id')
            ->get();

        if ($mosques->isEmpty()) {
            throw new \Exception(
                '❌ لا توجد مساجد في قاعدة البيانات. شغّل MosqueSeeder أولاً.'
            );
        }

        $this->command->info(
            "🕌 تم العثور على {$mosques->count()} مسجد."
        );

        $createdCount = 0;
        $existingCount = 0;

        /*
         * عدد المساحات لكل مسجد.
         *
         * سننشئ 5 مساحات لكل مسجد.
         */
        $spacesPerMosque = 5;

        foreach ($mosques as $mosqueIndex => $mosque) {

            $this->command->info('');
            $this->command->info(
                "🕌 المسجد: {$mosque->name} (ID: {$mosque->id})"
            );

            /*
             * نعمل shuffle حتى تحصل المساجد
             * على مساحات مختلفة.
             */
            $selectedSpaces = collect($this->spaces)
                ->shuffle()
                ->take($spacesPerMosque);

            foreach ($selectedSpaces as $spaceData) {

                /*
                 * منع تكرار نفس المساحة في نفس المسجد.
                 */
                $existing = MosqueSpace::where(
                    'mosque_id',
                    $mosque->id
                )
                    ->where(
                        'name',
                        $spaceData['name']
                    )
                    ->first();

                if ($existing) {

                    $existingCount++;

                    $this->command->line(
                        "   ↪ موجود: {$spaceData['name']}"
                    );

                    continue;
                }

                /*
                 * إنشاء المساحة.
                 */
                $space = MosqueSpace::create([
                    'mosque_id' => $mosque->id,

                    'name' => $spaceData['name'],

                    'capacity' => $spaceData['capacity'],

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);

                $createdCount++;

                $this->command->line(
                    "   ✅ {$space->name} | السعة: {$space->capacity}"
                );
            }
        }

        /*
         * الملخص.
         */
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 ملخص Mosque Spaces');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->command->info(
            "🕌 عدد المساجد: {$mosques->count()}"
        );

        $this->command->info(
            "➕ المساحات الجديدة: {$createdCount}"
        );

        $this->command->info(
            "↪ المساحات الموجودة مسبقاً: {$existingCount}"
        );

        $this->command->info(
            '📋 إجمالي المساحات: ' . MosqueSpace::count()
        );

        $this->command->info(
            '👥 إجمالي الطاقة الاستيعابية: ' .
            MosqueSpace::sum('capacity')
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info(
            '✅ تم تنفيذ MosqueSpaceSeeder بنجاح.'
        );
    }
}
