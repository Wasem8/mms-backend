<?php

namespace Modules\Community\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Community\Models\DawahProgram;
use Modules\Community\Models\ProgramSchedule;
use Modules\Donation\Models\Campaign;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;

class DawahAndCampaignSeeder extends Seeder
{
    /**
     * Sample data used to create a few varied records per mosque.
     */
    private array $programTemplates = [
        [
            'program_name' => 'مجلس علمي أسبوعي',
            'type' => 'lecture',
            'status' => 'active',
            'level' => 'beginner',
            'presenter' => 'الشيخ محمد العلي',
            'description' => 'مجلس أسبوعي لشرح مسائل العقيدة والفقه للمسلمين.',
        ],
        [
            'program_name' => 'دورة تحفيظ القرآن الكريم',
            'type' => 'course',
            'status' => 'active',
            'level' => 'intermediate',
            'presenter' => 'الأستاذ أحمد الحسن',
            'description' => 'دورة متدرجة لحفظ وتجويد القرآن الكريم لجميع الأعمار.',
        ],
        [
            'program_name' => 'مسابقة القرآن الكريم السنوية',
            'type' => 'competition',
            'status' => 'inactive',
            'level' => 'advanced',
            'presenter' => 'لجنة الإشراف الدعوي',
            'description' => 'مسابقة سنوية لتشجيع الناشئة على حفظ كتاب الله.',
        ],
    ];

    private array $campaignTemplates = [
        [
            'title' => 'حملة صيانة مرافق المسجد',
            'status' => 'active',
            'priority' => 'high',
            'target_amount' => 500000,
            'description' => 'حملة لجمع التبرعات من أجل صيانة وتجديد مرافق المسجد.',
        ],
        [
            'title' => 'حملة تفطير الصائمين',
            'status' => 'active',
            'priority' => 'medium',
            'target_amount' => 250000,
            'description' => 'حملة لإفطار الصائمين خلال شهر رمضان المبارك.',
        ],
        [
            'title' => 'حملة كسوة الشتاء',
            'status' => 'paused',
            'priority' => 'low',
            'target_amount' => 150000,
            'description' => 'حملة لتوزيع الملابس الشتوية على العائلات المحتاجة.',
        ],
    ];

    public function run(): void
    {
        $mosques = Mosque::all();

        foreach ($mosques as $mosque) {
            $space = MosqueSpace::where('mosque_id', $mosque->id)->first()
                ?? MosqueSpace::create([
                    'mosque_id' => $mosque->id,
                    'name' => 'القاعة الرئيسية',
                    'capacity' => 100,
                ]);

            $this->seedDawahPrograms($mosque->id, $space->id);
            $this->seedCampaigns($mosque->id);
        }
    }

    private function seedDawahPrograms(int $mosqueId, int $spaceId): void
    {
        if (DawahProgram::where('mosque_id', $mosqueId)->exists()) {
            return;
        }

        foreach ($this->programTemplates as $index => $template) {
            $program = DawahProgram::create(array_merge($template, [
                'mosque_id' => $mosqueId,
                'space_id' => $spaceId,
            ]));

            ProgramSchedule::create([
                'dawah_program_id' => $program->id,
                'title' => $template['program_name'],
                'notes' => null,
                'date' => now()->addDays($index * 7 + 1)->toDateString(),
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
            ]);
        }
    }

    private function seedCampaigns(int $mosqueId): void
    {
        if (Campaign::where('mosque_id', $mosqueId)->exists()) {
            return;
        }

        foreach ($this->campaignTemplates as $index => $template) {
            Campaign::create(array_merge($template, [
                'mosque_id' => $mosqueId,
                'collected_amount' => 0,
                'start_date' => now()->subDays($index * 5)->toDateString(),
                'end_date' => now()->addDays(30 - $index * 5)->toDateString(),
            ]));
        }
    }
}
