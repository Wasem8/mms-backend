<?php

namespace Modules\Donation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque;
use Modules\Donation\Models\Campaign;

class CampaignSeeder extends Seeder
{
    /**
     * حملات واقعية يمكن أن تطلقها المساجد.
     */
    private array $campaigns = [
        [
            'title' => 'حملة ترميم وصيانة المسجد',
            'description' => 'حملة للمساهمة في أعمال ترميم وصيانة مرافق المسجد وتحسين حالته العامة.',
            'target_amount' => 25000000,
            'priority' => 'high',
        ],
        [
            'title' => 'حملة تجهيز قاعات تحفيظ القرآن',
            'description' => 'توفير التجهيزات والمستلزمات اللازمة لقاعات تحفيظ القرآن الكريم.',
            'target_amount' => 15000000,
            'priority' => 'high',
        ],
        [
            'title' => 'حملة دعم حلقات القرآن',
            'description' => 'دعم برامج تحفيظ القرآن الكريم وتأمين احتياجات المعلمين والطلاب.',
            'target_amount' => 18000000,
            'priority' => 'medium',
        ],
        [
            'title' => 'حملة تأمين التدفئة الشتوية',
            'description' => 'تأمين مستلزمات التدفئة والوقود للمسجد خلال فصل الشتاء.',
            'target_amount' => 22000000,
            'priority' => 'high',
        ],
        [
            'title' => 'حملة تجهيز مصلى النساء',
            'description' => 'تجهيز وتحسين مصلى النساء وتوفير المستلزمات الأساسية.',
            'target_amount' => 12000000,
            'priority' => 'medium',
        ],
        [
            'title' => 'حملة الطاقة الشمسية',
            'description' => 'المساهمة في تركيب منظومة طاقة شمسية لتأمين الكهرباء للمسجد.',
            'target_amount' => 45000000,
            'priority' => 'high',
        ],
        [
            'title' => 'حملة تجهيز مكتبة المسجد',
            'description' => 'إنشاء وتجهيز مكتبة إسلامية تحتوي على كتب القرآن والتفسير والحديث والفقه.',
            'target_amount' => 8000000,
            'priority' => 'low',
        ],
        [
            'title' => 'حملة إفطار الصائمين',
            'description' => 'توفير وجبات إفطار للصائمين ورواد المسجد خلال شهر رمضان المبارك.',
            'target_amount' => 30000000,
            'priority' => 'high',
        ],
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🔄 بدء إنشاء حملات التبرع...');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        /*
        |--------------------------------------------------------------------------
        | 1. جلب المساجد
        |--------------------------------------------------------------------------
        */

        $mosques = Mosque::query()
            ->orderBy('id')
            ->get();

        if ($mosques->isEmpty()) {
            throw new \Exception(
                '❌ لا توجد مساجد. شغّل MosqueSeeder أولاً.'
            );
        }

        $this->command->info(
            "🕌 تم العثور على {$mosques->count()} مسجد."
        );

        $createdCount = 0;
        $existingCount = 0;

        /*
        |--------------------------------------------------------------------------
        | 2. إنشاء الحملات
        |--------------------------------------------------------------------------
        */

        foreach ($mosques as $mosqueIndex => $mosque) {

            $this->command->info('');
            $this->command->info(
                "🕌 المسجد: {$mosque->name} (ID: {$mosque->id})"
            );

            /*
             * ننشئ 3 حملات لكل مسجد.
             *
             * نغير بداية الاختيار حسب المسجد
             * حتى لا تحصل كل المساجد على نفس الحملات.
             */
            $campaignTemplates = collect($this->campaigns)
                ->shuffle()
                ->take(3);

            foreach ($campaignTemplates as $template) {

                $startDate = now()
                    ->subDays(rand(5, 60))
                    ->startOfDay();

                $endDate = (clone $startDate)
                    ->addDays(rand(30, 90))
                    ->startOfDay();

                /*
                 * نسبة الإنجاز تختلف من حملة لأخرى.
                 */
                $progress = collect([
                    0.15,
                    0.25,
                    0.35,
                    0.45,
                    0.60,
                    0.75,
                    0.90,
                ])->random();

                $targetAmount = (float) $template['target_amount'];

                $collectedAmount = round(
                    $targetAmount * $progress,
                    2
                );

                /*
                 * تحديد حالة الحملة.
                 */
                if ($collectedAmount >= $targetAmount) {
                    $status = 'completed';
                    $collectedAmount = $targetAmount;
                } else {
                    $status = collect([
                        'active',
                        'active',
                        'active',
                        'paused',
                    ])->random();
                }

                /*
                 * منع تكرار نفس الحملة لنفس المسجد.
                 */
                $exists = Campaign::where(
                    'mosque_id',
                    $mosque->id
                )
                    ->where(
                        'title',
                        $template['title']
                    )
                    ->exists();

                if ($exists) {

                    $existingCount++;

                    $this->command->line(
                        "   ↪ موجودة: {$template['title']}"
                    );

                    continue;
                }

                Campaign::create([
                    'mosque_id' => $mosque->id,

                    'title' => $template['title'],

                    'description' => $template['description'],

                    'target_amount' => $targetAmount,

                    'collected_amount' => $collectedAmount,

                    'status' => $status,

                    'start_date' => $startDate->toDateString(),

                    'end_date' => $endDate->toDateString(),

                    'priority' => $template['priority'],

                    'cover_image' => null,
                ]);

                $createdCount++;

                $this->command->line(
                    "   ✅ {$template['title']} | الهدف: {$targetAmount} | المحصل: {$collectedAmount}"
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. الملخص
        |--------------------------------------------------------------------------
        */

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 ملخص Campaigns');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->command->info(
            "🕌 عدد المساجد: {$mosques->count()}"
        );

        $this->command->info(
            "➕ الحملات الجديدة: {$createdCount}"
        );

        $this->command->info(
            "↪ الحملات الموجودة مسبقاً: {$existingCount}"
        );

        $this->command->info(
            '📢 إجمالي الحملات: ' . Campaign::count()
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info(
            '✅ تم تنفيذ CampaignSeeder بنجاح.'
        );
    }
}
