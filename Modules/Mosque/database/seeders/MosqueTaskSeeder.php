<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Enums\MosqueTaskCategory;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueTask;
use Modules\User\Models\User;

class MosqueTaskSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::whereHas('roles', fn($q) => $q->where('name', 'mosque_manager'))
            ->first()
            ?? User::first();

        if (!$creator) {
            $this->command->warn('لا يوجد مستخدم لإنشاء المهام، تم تخطي بذر المهام.');

            return;
        }

        // [العنوان, التصنيف, مكتملة؟, مهمة؟]
        $templates = [
            ['عقد صيانة دورية للمكيفات', MosqueTaskCategory::Maintenance, false, false],
            ['تنظيف المصلى الرئيسي', MosqueTaskCategory::Cleaning, true, false],
            ['إقامة درس أسبوعي في القرآن الكريم', MosqueTaskCategory::Activity, false, true],
            ['تجهيز المصلى لصلاة العيد', MosqueTaskCategory::PrayerWorship, false, false],
            ['مراجعة سجلات التبرعات الشهرية', MosqueTaskCategory::Administrative, false, false],
            ['صيانة دورات المياه', MosqueTaskCategory::Maintenance, true, true],
            ['ترتيب مكتبة المسجد', MosqueTaskCategory::Cleaning, false, false],
            ['تنظيم حملة نظافة الحي', MosqueTaskCategory::Activity, false, false],
        ];

        $mosques = Mosque::all();
        $count = 0;
        $index = 0;

        foreach ($mosques as $mosque) {
            foreach ($templates as [$title, $category, $completed, $important]) {
                // تاريخ استحقاق ثابت حسب الترتيب كي لا تتكرر المهام عند إعادة البذر
                $dueDate = now()->addDays(($index % 30) + 1)->toDateString();

                MosqueTask::updateOrCreate(
                    [
                        'mosque_id' => $mosque->id,
                        'title' => $title,
                        'due_date' => $dueDate,
                    ],
                    [
                        'created_by' => $creator->id,
                        'category' => $category,
                        'due_time' => null,
                        'is_completed' => $completed,
                        'completed_at' => $completed ? now() : null,
                        'is_important' => $important,
                        'notes' => null,
                    ]
                );

                $count++;
                $index++;
            }
        }

        $this->command->info('تم إنشاء/تحديث ' . $count . ' مهمة في مساجد دمشق.');
    }
}
