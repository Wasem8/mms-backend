<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueNeed;


class MosqueNeedSeeder extends Seeder
{
    /**
     * احتياجات واقعية يمكن استخدامها كبيانات تجريبية.
     */
    private array $needs = [
        [
            'title' => 'صيانة أجهزة التكييف',
            'description' => 'الحاجة إلى صيانة أجهزة التكييف في المسجد وتجهيزها للعمل بكفاءة خلال فصل الصيف.',
            'type' => 'maintenance',
            'target_amount' => 2500000,
            'is_urgent' => true,
        ],
        [
            'title' => 'شراء مصاحف جديدة',
            'description' => 'توفير مصاحف جديدة بأحجام مختلفة لاستبدال المصاحف القديمة وتلبية احتياجات المصلين وحلقات تحفيظ القرآن.',
            'type' => 'supplies',
            'target_amount' => 1800000,
            'is_urgent' => false,
        ],
        [
            'title' => 'تجهيز قاعة تحفيظ القرآن',
            'description' => 'تجهيز قاعة خاصة بحلقات تحفيظ القرآن الكريم بالطاولات والكراسي والخزائن والوسائل التعليمية.',
            'type' => 'equipment',
            'target_amount' => 4500000,
            'is_urgent' => true,
        ],
        [
            'title' => 'ترميم دورات المياه',
            'description' => 'إجراء أعمال صيانة وترميم لدورات المياه وتجديد الأدوات الصحية وشبكة المياه.',
            'type' => 'maintenance',
            'target_amount' => 3200000,
            'is_urgent' => true,
        ],
        [
            'title' => 'شراء سجاد جديد للمسجد',
            'description' => 'استبدال السجاد القديم وتوفير سجاد جديد مناسب لمساحة المسجد.',
            'type' => 'equipment',
            'target_amount' => 6000000,
            'is_urgent' => false,
        ],
        [
            'title' => 'توفير مياه الشرب',
            'description' => 'تأمين مياه الشرب للمصلين بشكل مستمر خلال أيام الأسبوع وخاصة في أيام الجمعة.',
            'type' => 'supplies',
            'target_amount' => 900000,
            'is_urgent' => false,
        ],
        [
            'title' => 'إصلاح نظام الإنارة',
            'description' => 'إصلاح واستبدال وحدات الإنارة التالفة داخل المسجد وفي الساحات والمداخل.',
            'type' => 'maintenance',
            'target_amount' => 2100000,
            'is_urgent' => true,
        ],
        [
            'title' => 'شراء كراسي لكبار السن',
            'description' => 'توفير كراسٍ مناسبة لكبار السن وذوي الاحتياجات الخاصة لأداء الصلاة براحة.',
            'type' => 'equipment',
            'target_amount' => 1500000,
            'is_urgent' => false,
        ],
        [
            'title' => 'تجهيز مكتبة المسجد',
            'description' => 'تجهيز مكتبة تحتوي على كتب القرآن والتفسير والحديث والسيرة والعلوم الإسلامية.',
            'type' => 'supplies',
            'target_amount' => 2800000,
            'is_urgent' => false,
        ],
        [
            'title' => 'صيانة نظام الصوت',
            'description' => 'صيانة مكبرات الصوت والميكروفونات ونظام الصوت الداخلي والخارجي للمسجد.',
            'type' => 'maintenance',
            'target_amount' => 1700000,
            'is_urgent' => true,
        ],
        [
            'title' => 'تجهيز غرفة المعلمين',
            'description' => 'تجهيز غرفة خاصة بمعلمي حلقات القرآن وتوفير المكاتب والكراسي والخزائن اللازمة.',
            'type' => 'equipment',
            'target_amount' => 2400000,
            'is_urgent' => false,
        ],
        [
            'title' => 'تركيب أبواب ونوافذ جديدة',
            'description' => 'استبدال بعض الأبواب والنوافذ التالفة في مبنى المسجد وتحسين العزل والحماية.',
            'type' => 'maintenance',
            'target_amount' => 5200000,
            'is_urgent' => true,
        ],
        [
            'title' => 'شراء مستلزمات النظافة',
            'description' => 'توفير مواد وأدوات النظافة اللازمة للمسجد ودورات المياه بشكل دوري.',
            'type' => 'supplies',
            'target_amount' => 1200000,
            'is_urgent' => false,
        ],
        [
            'title' => 'تركيب كاميرات مراقبة',
            'description' => 'تركيب نظام كاميرات مراقبة للمداخل والساحات والمرافق الخارجية للمسجد.',
            'type' => 'equipment',
            'target_amount' => 7500000,
            'is_urgent' => false,
        ],
        [
            'title' => 'دعم مالي لحلقات القرآن',
            'description' => 'توفير الدعم المالي اللازم لتغطية احتياجات حلقات تحفيظ القرآن الكريم والأنشطة التعليمية.',
            'type' => 'financial',
            'target_amount' => 5000000,
            'is_urgent' => true,
        ],
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🔄 بدء إنشاء احتياجات المساجد...');
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
         * ننشئ 5 احتياجات مختلفة لكل مسجد.
         *
         * نستخدم توزيعاً مختلفاً للاحتياجات بين المساجد
         * حتى لا تكون جميع المساجد متطابقة.
         */
        foreach ($mosques as $mosqueIndex => $mosque) {

            $this->command->info('');
            $this->command->info(
                "🕌 المسجد: {$mosque->name} (ID: {$mosque->id})"
            );

            /*
             * عدد الاحتياجات لكل مسجد.
             *
             * يمكن تغييره مثلاً إلى 3 أو 8.
             */
            $needsPerMosque = 5;

            /*
             * نستخدم offset مختلف لكل مسجد حتى يحصل
             * كل مسجد على مجموعة مختلفة من الاحتياجات.
             */
            $availableNeeds = collect($this->needs)
                ->shuffle()
                ->take($needsPerMosque);

            foreach ($availableNeeds as $needData) {

                /*
                 * نتأكد أن نفس المسجد لا يحصل على
                 * نفس عنوان الاحتياج أكثر من مرة.
                 */
                $existing = MosqueNeed::where('mosque_id', $mosque->id)
                    ->where('title', $needData['title'])
                    ->first();

                if ($existing) {

                    $existingCount++;

                    $this->command->line(
                        "   ↪ موجود: {$needData['title']}"
                    );

                    continue;
                }

                /*
                 * نحدد نسبة إنجاز عشوائية للبيانات التجريبية.
                 *
                 * 0%  -> open
                 * 20-70% -> partially_fulfilled
                 * 100% -> fulfilled
                 */
                $statusType = rand(1, 10);

                if ($statusType <= 5) {

                    // احتياج مفتوح
                    $status = 'open';

                    $collectedAmount = 0;

                } elseif ($statusType <= 9) {

                    // احتياج ممول جزئياً
                    $status = 'partially_fulfilled';

                    $percentage = rand(20, 70);

                    $collectedAmount = round(
                        $needData['target_amount'] *
                        ($percentage / 100),
                        2
                    );

                } else {

                    // احتياج مكتمل
                    $status = 'fulfilled';

                    $collectedAmount = $needData['target_amount'];
                }

                $need = MosqueNeed::create([
                    'mosque_id' => $mosque->id,

                    'title' => $needData['title'],

                    'description' => $needData['description'],

                    'type' => $needData['type'],

                    /*
                     * لا نحتاج صوراً حالياً.
                     *
                     * يمكن لاحقاً ربطها مع Supabase Storage.
                     */
                    'image' => null,

                    'target_amount' => $needData['target_amount'],

                    'collected_amount' => $collectedAmount,

                    'status' => $status,

                    'is_urgent' => $needData['is_urgent'],

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);

                $createdCount++;

                $this->command->line(
                    "   ✅ {$need->title} | {$status} | {$collectedAmount} / {$needData['target_amount']} SYP"
                );
            }
        }

        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 ملخص Mosque Needs');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->command->info(
            "🕌 عدد المساجد: {$mosques->count()}"
        );

        $this->command->info(
            "➕ الاحتياجات الجديدة: {$createdCount}"
        );

        $this->command->info(
            "↪ الاحتياجات الموجودة مسبقاً: {$existingCount}"
        );

        $this->command->info(
            '📋 إجمالي الاحتياجات: ' . MosqueNeed::count()
        );

        $this->command->info(
            '🔴 الاحتياجات العاجلة: ' .
            MosqueNeed::where('is_urgent', true)->count()
        );

        $this->command->info(
            '💰 الاحتياجات المالية: ' .
            MosqueNeed::where('type', 'financial')->count()
        );

        $this->command->info(
            '🔧 احتياجات الصيانة: ' .
            MosqueNeed::where('type', 'maintenance')->count()
        );

        $this->command->info(
            '🪑 احتياجات التجهيزات: ' .
            MosqueNeed::where('type', 'equipment')->count()
        );

        $this->command->info(
            '📦 احتياجات المستلزمات: ' .
            MosqueNeed::where('type', 'supplies')->count()
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->command->info(
            '✅ تم تنفيذ MosqueNeedSeeder بنجاح.'
        );
    }
}
