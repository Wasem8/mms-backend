<?php

namespace Modules\Donation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Donation\Models\Campaign;
use Modules\Mosque\Models\Mosque;

class CampaignSeeder extends Seeder
{
    private const CAMPAIGN_TEMPLATES = [
        ['ترميم المسجد', 'حملة لجمع التبرعات لترميم المسجد وإعادة تأهيله من الداخل والخارج.'],
        ['شراء فرش ومصاحف جديدة', 'توفير فرش ومصاحف جديدة للمحافظة على نظافة المصلى وراحة المصلين.'],
        ['تركيب مكيفات', 'تركيب مكيفات في قاعة الصلاة لتوفير الراحة خلال أشهر الصيف.'],
        ['توسعة المصلى', 'توسعة المصلى لاستيعاب المزيد من المصلين في صلاة الجمعة والعيدين.'],
        ['مشروع المياه والوضوء', 'تجديد شبكة المياه وتأهيل أماكن الوضوء في المسجد.'],
        ['صيانة دورية للمرافق', 'صيانة المرافق العامة للمسجد ودورات المياه بشكل دوري.'],
        ['حملة إفطار صائم', 'تمويل وجبات إفطار للصائمين في شهر رمضان المبارك.'],
        ['تجهيز قاعة تحفيظ القرآن', 'تجهيز قاعة تحفيظ القرآن بالمقاعد والسبورات والوسائل التعليمية.'],
    ];

    public function run(): void
    {
        $mosques = Mosque::all();

        if ($mosques->isEmpty()) {
            $this->command?->warn('لا توجد مساجد بعد — تجاوز بذر حملات التبرع. قم ببذر وحدة المساجد أولاً.');

            return;
        }

        $count = 0;

        foreach ($mosques as $mosque) {
            // مسجدان من الحملات لكل مسجد بشكل ثابت (كي لا تتكرر عند إعادة البذر)
            $first = self::CAMPAIGN_TEMPLATES[($mosque->id - 1) % count(self::CAMPAIGN_TEMPLATES)];
            $second = self::CAMPAIGN_TEMPLATES[($mosque->id + 2) % count(self::CAMPAIGN_TEMPLATES)];

            $this->createCampaign($mosque, $first, 'active', rand(300000, 1500000), rand(50000, 400000));
            $this->createCampaign($mosque, $second, 'completed', rand(100000, 600000), null);

            $count += 2;
        }

        $this->command?->info('تم إنشاء/تحديث ' . $count . ' حملة تبرع لمساجد دمشق.');
    }

    private function createCampaign(Mosque $mosque, array $template, string $status, int $target, ?int $collected): void
    {
        $collectedAmount = $collected === null
            ? $target
            : min($collected, $target);

        Campaign::updateOrCreate(
            [
                'mosque_id' => $mosque->id,
                'title' => $template[0],
            ],
            [
                'description' => $template[1],
                'target_amount' => $target,
                'collected_amount' => $collectedAmount,
                'status' => $status,
                'start_date' => now()->subMonths(rand(1, 6))->startOfMonth(),
                'end_date' => $status === 'completed' ? now()->subDays(rand(10, 40)) : now()->addMonths(rand(1, 6)),
                'priority' => $status === 'completed' ? 'medium' : (rand(0, 1) ? 'high' : 'medium'),
            ]
        );
    }
}
