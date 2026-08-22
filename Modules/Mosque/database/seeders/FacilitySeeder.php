<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\Facility;

class FacilitySeeder extends Seeder
{
    /**
     * المرافق المتوفرة في المساجد.
     */
    private array $facilities = [
        'مكبرات صوت ونظام صوتي',
        'تكييف مركزي',
        'مراوح سقفية',
        'تدفئة مركزية',
        'مولد كهرباء',
        'ألواح طاقة شمسية',
        'خزان مياه',
        'مواقف سيارات',
        'دورات مياه',
        'مصلى نساء',
        'مكتبة',
        'قاعة تحفيظ القرآن',
        'قاعة محاضرات',
        'غرفة وضوء',
        'منطقة وضوء للنساء',
        'ساحة خارجية',
        'نظام مراقبة وكاميرات',
        'شبكة إنترنت',
        'غرفة إسعافات أولية',
        'مستودع',
        'مطبخ',
        'قاعة أنشطة',
        'شاشات عرض',
        'نظام إنذار وحماية من الحريق',
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🔄 بدء إنشاء مرافق المساجد...');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        /*
        |--------------------------------------------------------------------------
        | 1. جلب المساجد الموجودة مسبقاً
        |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | 2. إنشاء المرافق
        |--------------------------------------------------------------------------
        */

        $facilityModels = collect();

        foreach ($this->facilities as $facilityName) {

            $facility = Facility::firstOrCreate(
                [
                    'name' => $facilityName,
                ]
            );

            $facilityModels->push($facility);
        }

        $this->command->info(
            '🏢 تم تجهيز ' . $facilityModels->count() . ' مرفق.'
        );

        /*
        |--------------------------------------------------------------------------
        | 3. ربط المرافق بالمساجد
        |--------------------------------------------------------------------------
        */

        $createdRelations = 0;
        $existingRelations = 0;

        /*
         * عدد المرافق لكل مسجد.
         *
         * يمكن تغييره مثلاً إلى 10 أو 15.
         */
        $facilitiesPerMosque = 10;

        foreach ($mosques as $mosqueIndex => $mosque) {

            $this->command->info('');
            $this->command->info(
                "🕌 المسجد: {$mosque->name} (ID: {$mosque->id})"
            );

            /*
             * نستخدم ترتيباً مختلفاً لكل مسجد
             * حتى لا تحصل جميع المساجد على نفس المرافق.
             */
            $selectedFacilities = $facilityModels
                ->shuffle()
                ->take($facilitiesPerMosque);

            foreach ($selectedFacilities as $facility) {

                /*
                 * التحقق من وجود العلاقة مسبقاً.
                 */
                $exists = DB::table('facility_mosque')
                    ->where('mosque_id', $mosque->id)
                    ->where('facility_id', $facility->id)
                    ->exists();

                if ($exists) {

                    $existingRelations++;

                    $this->command->line(
                        "   ↪ موجود: {$facility->name}"
                    );

                    continue;
                }

                /*
                 * إنشاء العلاقة.
                 */
                DB::table('facility_mosque')->insert([
                    'mosque_id' => $mosque->id,
                    'facility_id' => $facility->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $createdRelations++;

                $this->command->line(
                    "   ✅ {$facility->name}"
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4. الملخص
        |--------------------------------------------------------------------------
        */

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 ملخص Facilities');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->command->info(
            "🕌 عدد المساجد: {$mosques->count()}"
        );

        $this->command->info(
            '🏢 عدد المرافق: ' . $facilityModels->count()
        );

        $this->command->info(
            "➕ العلاقات الجديدة: {$createdRelations}"
        );

        $this->command->info(
            "↪ العلاقات الموجودة مسبقاً: {$existingRelations}"
        );

        $this->command->info(
            '🔗 إجمالي علاقات المرافق والمساجد: ' .
            DB::table('facility_mosque')->count()
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info(
            '✅ تم تنفيذ FacilitySeeder بنجاح.'
        );
    }
}
