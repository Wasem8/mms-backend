<?php

namespace Modules\Donation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Donation\Models\Campaign;
use Modules\Donation\Models\Donation;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class DonationSeeder extends Seeder
{
    private const DONOR_NAMES = [
        'أحمد محمد', 'خالد عمر', 'سارة أحمد', 'محمد علي', 'فاطمة الحسن',
        'عبد الرحمن سعيد', 'ليلى خليل', 'ياسر النعيمي', 'هدى الشامي', 'عمر فارس',
        'نورا يوسف', 'مؤسسة الخير', 'لجنة زكاة الحي', 'فاعل خير',
    ];

    private const IN_KIND_ITEMS = [
        'مواد غذائية متنوعة',
        'فرش ومصاحف',
        'مكيفات هواء',
        'أجهزة صوتية',
        'مواد تنظيف',
    ];

    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command?->warn('لا يوجد مستخدمون لربط التبرعات، تم تخطي البذر.');

            return;
        }

        $referenceCounter = 0;
        $count = 0;

        // تبرعات مرتبطة بحملات
        foreach (Campaign::all() as $campaign) {
            $donationsCount = 3 + ($campaign->id % 3);

            for ($i = 0; $i < $donationsCount; $i++) {
                $referenceCounter++;
                $count += $this->createDonation($referenceCounter, [
                    'mosque_id' => $campaign->mosque_id,
                    'campaign_id' => $campaign->id,
                    'user_id' => $userIds[($campaign->id + $i) % count($userIds)],
                ]);
            }
        }

        // تبرعات مستقلة (غير مرتبطة بحملة) لكل مسجد
        foreach (Mosque::all() as $mosque) {
            for ($i = 0; $i < 2; $i++) {
                $referenceCounter++;
                $count += $this->createDonation($referenceCounter, [
                    'mosque_id' => $mosque->id,
                    'campaign_id' => null,
                    'user_id' => $userIds[($mosque->id + $i) % count($userIds)],
                ]);
            }
        }

        $this->command?->info('تم إنشاء/تحديث ' . $count . ' تبرع لمساجد دمشق.');
    }

    /**
     * @param  array{mosque_id: int, campaign_id: ?int, user_id: int}  $context
     */
    private function createDonation(int $referenceCounter, array $context): int
    {
        $reference = 'DON-' . str_pad((string) $referenceCounter, 5, '0', STR_PAD_LEFT);

        // بعض التبرعات عينية وبعضها نقدي
        $isInKind = $referenceCounter % 4 === 0;

        $status = $referenceCounter % 7 === 0 ? 'pending' : 'completed';

        if ($isInKind) {
            $item = self::IN_KIND_ITEMS[$referenceCounter % count(self::IN_KIND_ITEMS)];
            $amount = null;
            $baseAmount = 0;
            $description = $item;
        } else {
            $item = null;
            $amount = [5000, 10000, 25000, 50000, 100000, 250000][$referenceCounter % 6];
            $baseAmount = $amount;
            $description = null;
        }

        $donorName = self::DONOR_NAMES[$referenceCounter % count(self::DONOR_NAMES)];
        $paymentMethod = $isInKind ? 'cash' : (($referenceCounter % 3 === 0) ? 'stripe' : 'cash');

        Donation::updateOrCreate(
            ['reference' => $reference],
            [
                'mosque_id' => $context['mosque_id'],
                'user_id' => $context['user_id'],
                'campaign_id' => $context['campaign_id'],
                'mosque_need_id' => null,
                'donation_type' => $isInKind ? 'in_kind' : 'cash',
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'currency' => 'SYP',
                'exchange_rate' => 1,
                'base_amount' => $baseAmount,
                'item_description' => $description,
                'donor_name' => $donorName,
                'status' => $status,
                'completed_at' => $status === 'completed' ? now()->subDays($referenceCounter % 60) : null,
            ]
        );

        return 1;
    }
}
