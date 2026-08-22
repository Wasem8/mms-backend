<?php

namespace Modules\Donation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueNeed;
use Modules\Donation\Models\Campaign;
use Modules\Donation\Models\Donation;

class DonationSeeder extends Seeder
{
    private array $donorNames = [
        'فاعل خير',
        'عبد الرحمن محمد',
        'أحمد خالد',
        'محمد محمود',
        'عمر عبد الله',
        'حسن علي',
        'مؤسسة الخير',
        'جمعية الإحسان',
        'فاعل خير مجهول',
        'مؤسسة الأمل',
        'عبد الكريم أحمد',
        'خالد مصطفى',
        'سلمان إبراهيم',
        'شركة الخير للتنمية',
        'تبرع عائلي',
    ];

    private array $inKindItems = [
        'سجاد للمصلى',
        'مراوح كهربائية',
        'مصاحف جديدة',
        'كراسي للطلاب',
        'طاولات تعليمية',
        'مكبرات صوت',
        'مستلزمات تنظيف',
        'أجهزة إنارة LED',
        'خزانات مياه',
        'مستلزمات مكتبية',
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🔄 بدء إنشاء التبرعات...');
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

        /*
        |--------------------------------------------------------------------------
        | 2. جلب الحملات والاحتياجات
        |--------------------------------------------------------------------------
        */

        $campaigns = Campaign::query()
            ->get()
            ->groupBy('mosque_id');

        $needs = MosqueNeed::query()
            ->get()
            ->groupBy('mosque_id');

        $this->command->info(
            "🕌 المساجد: {$mosques->count()}"
        );

        $this->command->info(
            '📢 الحملات: ' . Campaign::count()
        );

        $this->command->info(
            '📋 الاحتياجات: ' . MosqueNeed::count()
        );

        /*
        |--------------------------------------------------------------------------
        | 3. إنشاء التبرعات
        |--------------------------------------------------------------------------
        */

        $createdCount = 0;

        foreach ($mosques as $mosque) {

            $this->command->info('');
            $this->command->info(
                "🕌 المسجد: {$mosque->name} (ID: {$mosque->id})"
            );

            $mosqueCampaigns = $campaigns->get(
                $mosque->id,
                collect()
            );

            $mosqueNeeds = $needs->get(
                $mosque->id,
                collect()
            );

            /*
             * 10 تبرعات تقريباً لكل مسجد.
             */
            $donationsCount = 10;

            for ($i = 1; $i <= $donationsCount; $i++) {

                /*
                |--------------------------------------------------------------------------
                | تحديد نوع التبرع
                |--------------------------------------------------------------------------
                */

                $donationType = collect([
                    'cash',
                    'cash',
                    'cash',
                    'cash',
                    'cash',
                    'in_kind',
                ])->random();

                /*
                |--------------------------------------------------------------------------
                | تحديد حملة أو احتياج
                |--------------------------------------------------------------------------
                */

                $campaign = null;
                $need = null;

                if ($mosqueCampaigns->isNotEmpty()) {

                    $campaign = $mosqueCampaigns->random();
                }

                if ($mosqueNeeds->isNotEmpty()) {

                    $need = $mosqueNeeds->random();
                }

                /*
                |--------------------------------------------------------------------------
                | التبرع النقدي
                |--------------------------------------------------------------------------
                */

                if ($donationType === 'cash') {

                    $amount = collect([
                        25000,
                        50000,
                        75000,
                        100000,
                        150000,
                        250000,
                        500000,
                        750000,
                        1000000,
                        2500000,
                    ])->random();

                    $itemDescription = null;

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | التبرع العيني
                    |--------------------------------------------------------------------------
                    */

                    $amount = null;

                    $itemDescription = collect(
                        $this->inKindItems
                    )->random();
                }

                /*
                |--------------------------------------------------------------------------
                | طريقة الدفع
                |--------------------------------------------------------------------------
                */

                if ($donationType === 'cash') {

                    $paymentMethod = collect([
                        'cash',
                        'cash',
                        'cash',
                        'stripe',
                    ])->random();

                } else {

                    /*
                     * التبرع العيني لا يحتاج Stripe.
                     */
                    $paymentMethod = 'cash';
                }

                /*
                |--------------------------------------------------------------------------
                | الحالة
                |--------------------------------------------------------------------------
                */

                $status = collect([
                    'completed',
                    'completed',
                    'completed',
                    'completed',
                    'pending',
                ])->random();

                /*
                |--------------------------------------------------------------------------
                | إذا كان Stripe، نجعله completed
                |--------------------------------------------------------------------------
                */

                $stripePaymentIntentId = null;

                if ($paymentMethod === 'stripe') {

                    $status = 'completed';

                    $stripePaymentIntentId =
                        'pi_test_' . Str::random(24);
                }

                $completedAt = $status === 'completed'
                    ? now()->subDays(rand(0, 30))
                    : null;

                /*
                |--------------------------------------------------------------------------
                | العملة وسعر الصرف
                |--------------------------------------------------------------------------
                */

                $currency = 'SYP';

                $exchangeRate = 1.0000;

                $baseAmount = $amount ?? 0;

                /*
                |--------------------------------------------------------------------------
                | Reference فريد
                |--------------------------------------------------------------------------
                */

                $reference =
                    'DON-' .
                    now()->format('Ymd') .
                    '-' .
                    strtoupper(Str::random(8));

                /*
                |--------------------------------------------------------------------------
                | إنشاء التبرع
                |--------------------------------------------------------------------------
                */

                Donation::create([
                    'reference' => $reference,

                    'mosque_id' => $mosque->id,

                    /*
                     * نربطه بالحملة أو الاحتياج.
                     */
                    'mosque_need_id' => $need?->id,

                    'campaign_id' => $campaign?->id,

                    'donation_type' => $donationType,

                    'payment_method' => $paymentMethod,

                    'amount' => $amount,

                    'currency' => $currency,

                    'exchange_rate' => $exchangeRate,

                    'base_amount' => $baseAmount,

                    'item_description' => $itemDescription,

                    'donor_name' => collect(
                        $this->donorNames
                    )->random(),

                    'status' => $status,

                    'completed_at' => $completedAt,

                    'stripe_payment_intent_id' =>
                        $stripePaymentIntentId,
                ]);

                $createdCount++;
            }

            $this->command->line(
                "   ✅ تم إنشاء {$donationsCount} تبرعات"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. الملخص
        |--------------------------------------------------------------------------
        */

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 ملخص Donations');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->command->info(
            "🕌 عدد المساجد: {$mosques->count()}"
        );

        $this->command->info(
            "💰 التبرعات الجديدة: {$createdCount}"
        );

        $this->command->info(
            '💵 إجمالي التبرعات: ' . Donation::count()
        );

        $this->command->info(
            '💰 إجمالي التبرعات النقدية المكتملة: ' .
            number_format(
                Donation::where('donation_type', 'cash')
                    ->where('status', 'completed')
                    ->sum('amount')
            ) .
            ' SYP'
        );

        $this->command->info(
            '⏳ التبرعات المعلقة: ' .
            Donation::where('status', 'pending')->count()
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info(
            '✅ تم تنفيذ DonationSeeder بنجاح.'
        );
    }
}
