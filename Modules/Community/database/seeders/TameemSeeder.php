<?php

namespace Modules\Community\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;
use Modules\User\Models\Role;

class TameemSeeder extends Seeder
{
    /**
     * تعاميم واقعية.
     */
    private array $tameems = [

        [
            'title' => 'تعميم بشأن تنظيم صلاة الجمعة',
            'content' => 'يرجى من جميع مديري المساجد الالتزام بتنظيم صلاة الجمعة وفق الأوقات والتعليمات المعتمدة، والتأكد من جاهزية المسجد والمرافق قبل وصول المصلين.',
        ],

        [
            'title' => 'تعميم بشأن نظافة المساجد',
            'content' => 'يرجى التأكد من المحافظة على نظافة المسجد ومرافقه بشكل مستمر، مع متابعة نظافة دورات المياه ومصليات النساء والساحات والمداخل.',
        ],

        [
            'title' => 'تعميم بشأن برامج تحفيظ القرآن الكريم',
            'content' => 'يرجى متابعة برامج تحفيظ القرآن الكريم والحلقات التعليمية في المساجد، والتأكد من انتظام الطلاب والمعلمين وتحديث بيانات الحلقات بشكل دوري.',
        ],

        [
            'title' => 'تعميم بشأن صيانة مرافق المسجد',
            'content' => 'يرجى رفع طلبات الصيانة المتعلقة بالكهرباء والمياه والتكييف وأنظمة الصوت والتجهيزات الأخرى فور ظهور أي عطل، وذلك لضمان استمرار الخدمات المقدمة للمصلين.',
        ],

        [
            'title' => 'تعميم بشأن خطبة الجمعة',
            'content' => 'يرجى الالتزام بالموضوعات المعتمدة لخطب الجمعة والتأكد من جاهزية الخطيب ومرافق المسجد قبل موعد الصلاة.',
        ],

        [
            'title' => 'تعميم بشأن تنظيم البرامج الدعوية',
            'content' => 'يرجى من مديري المساجد تنظيم البرامج والمحاضرات الدعوية وفق الجدول المعتمد، وإدخال مواعيد البرامج وتحديثها في النظام.',
        ],

        [
            'title' => 'تعميم بشأن استقبال التبرعات',
            'content' => 'يرجى توثيق جميع التبرعات الواردة للمسجد في النظام مع تحديد نوع التبرع وقيمته والغرض منه، والمحافظة على المستندات المتعلقة بالتبرعات.',
        ],

        [
            'title' => 'تعميم بشأن احتياجات المساجد',
            'content' => 'يرجى تحديث قائمة احتياجات المسجد بشكل مستمر، وتحديد الاحتياجات العاجلة والأولوية لكل طلب لتسهيل متابعتها ومعالجتها.',
        ],

        [
            'title' => 'تعميم بشأن السلامة داخل المساجد',
            'content' => 'يرجى التأكد من سلامة مخارج الطوارئ وأجهزة الإطفاء والتوصيلات الكهربائية وممرات الحركة، والإبلاغ عن أي خطر قد يؤثر على سلامة المصلين.',
        ],

        [
            'title' => 'تعميم بشأن أنشطة الأطفال والشباب',
            'content' => 'يرجى تشجيع إقامة الأنشطة التعليمية والتربوية المناسبة للأطفال والشباب، مع مراعاة التنظيم الجيد وتوفير بيئة آمنة ومناسبة للمشاركين.',
        ],

        [
            'title' => 'تعميم بشأن تحديث بيانات المسجد',
            'content' => 'يرجى من مديري المساجد مراجعة بيانات المسجد والمرافق والمساحات والبرامج والاحتياجات والتأكد من أن جميع المعلومات المسجلة في النظام محدثة.',
        ],

        [
            'title' => 'تعميم بشأن الحضور والانصراف',
            'content' => 'يرجى متابعة سجلات الحضور والانصراف الخاصة بالعاملين والمعلمين بشكل دوري، والتأكد من تسجيل البيانات بشكل صحيح.',
        ],

        [
            'title' => 'تعميم بشأن تجهيز المساجد للمناسبات الدينية',
            'content' => 'يرجى تجهيز المساجد والمرافق التابعة لها قبل المناسبات الدينية والمواسم المهمة، والتأكد من توفر الاحتياجات الأساسية للمصلين.',
        ],

        [
            'title' => 'تعميم بشأن المحافظة على مرافق المسجد',
            'content' => 'يرجى توعية رواد المسجد بالمحافظة على الممتلكات والتجهيزات والمرافق وعدم استخدامها بطريقة تؤدي إلى تلفها أو تعطيلها.',
        ],

        [
            'title' => 'تعميم بشأن رفع التقارير الدورية',
            'content' => 'يرجى من جميع مديري المساجد متابعة التقارير الدورية ورفع البيانات المطلوبة في المواعيد المحددة، مع التأكد من صحة المعلومات قبل اعتمادها.',
        ],
    ];

    /**
     * عدد المستلمين لكل تعميم.
     */
    private int $recipientsPerTameem = 5;

    public function run(): void
    {
        DB::beginTransaction();

        try {

            $this->command->info('');
            $this->command->info(
                '🔄 بدء إنشاء التعاميم وربطها بمديري المساجد...'
            );
            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            // =========================================================
            // 1. جلب المساجد
            // =========================================================

            $mosques = Mosque::query()
                ->orderBy('id')
                ->get();

            if ($mosques->isEmpty()) {
                throw new \Exception(
                    '❌ لا توجد مساجد في قاعدة البيانات. شغّل MosqueSeeder أولاً.'
                );
            }

            $this->command->info(
                '🕌 تم العثور على ' .
                $mosques->count() .
                ' مسجد.'
            );

            // =========================================================
            // 2. جلب Role مدير المسجد
            // =========================================================

            $mosqueManagerRole = Role::where(
                'name',
                'mosque_manager'
            )->first();

            /*
             * بحث احتياطي في حال كان اسم الـ Role مختلفاً.
             */
            if (!$mosqueManagerRole) {

                $mosqueManagerRole = Role::query()
                    ->whereIn('name', [
                        'mosque_manager',
                        'manager',
                        'mosque-manager',
                        'مسؤول المسجد',
                        'مدير المسجد',
                    ])
                    ->first();
            }

            if (!$mosqueManagerRole) {
                throw new \Exception(
                    '❌ لم يتم العثور على Role الخاص بمدير المسجد. ' .
                    'تأكد من أن RoleSeeder أنشأ mosque_manager.'
                );
            }

            // =========================================================
            // 3. جلب مديري المساجد
            // =========================================================

            $managers = User::whereHas(
                'roles',
                function ($query) use ($mosqueManagerRole) {

                    $query->where(
                        'roles.id',
                        $mosqueManagerRole->id
                    );
                }
            )
                ->orderBy('id')
                ->get();

            if ($managers->isEmpty()) {
                throw new \Exception(
                    '❌ لا يوجد أي مستخدم بدور mosque_manager. ' .
                    'شغّل UserSeeder أولاً.'
                );
            }

            $this->command->info(
                '👤 مديرو المساجد الموجودون: ' .
                $managers->count()
            );

            // =========================================================
            // 4. إنشاء التعاميم
            // =========================================================

            $totalTameems = 0;
            $totalRecipients = 0;

            $managerCount = $managers->count();

            foreach ($this->tameems as $index => $tameemData) {

                // -----------------------------------------------------
                // اختيار المرسل
                // -----------------------------------------------------

                $sender = $managers[
                $index % $managerCount
                ];

                // -----------------------------------------------------
                // وقت الإرسال
                // -----------------------------------------------------

                $sentAt = Carbon::now()
                    ->subDays(
                        count($this->tameems) - $index
                    )
                    ->setTime(
                        9 + ($index % 4),
                        ($index * 10) % 60,
                        0
                    );

                // -----------------------------------------------------
                // منع تكرار التعميم
                // -----------------------------------------------------

                $existingTameem = DB::table('tameems')
                    ->where(
                        'title',
                        $tameemData['title']
                    )
                    ->first();

                if ($existingTameem) {

                    $tameemId = $existingTameem->id;

                    DB::table('tameems')
                        ->where('id', $tameemId)
                        ->update([
                            'content' => $tameemData['content'],
                            'sender_id' => $sender->id,
                            'sent_at' => $sentAt,
                            'updated_at' => now(),
                        ]);

                    $this->command->info(
                        "   ♻️ تحديث التعميم: {$tameemData['title']}"
                    );

                } else {

                    $tameemId = DB::table('tameems')
                        ->insertGetId([
                            'title' => $tameemData['title'],
                            'content' => $tameemData['content'],
                            'sender_id' => $sender->id,
                            'sent_at' => $sentAt,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $this->command->info(
                        "   📢 إنشاء: {$tameemData['title']}"
                    );
                }

                $totalTameems++;

                // =====================================================
                // 5. اختيار المستلمين
                // =====================================================

                $recipientCount = min(
                    $this->recipientsPerTameem,
                    $managerCount
                );

                /*
                 * توزيع دائري للمديرين.
                 *
                 * مثال إذا كان لدينا 10 مديرين:
                 *
                 * التعميم 1:
                 * 1,2,3,4,5
                 *
                 * التعميم 2:
                 * 2,3,4,5,6
                 *
                 * التعميم 3:
                 * 3,4,5,6,7
                 *
                 * وهكذا...
                 */

                $selectedManagers = collect();

                for (
                    $recipientIndex = 0;
                    $recipientIndex < $recipientCount;
                    $recipientIndex++
                ) {

                    $managerIndex =
                        ($index + $recipientIndex)
                        % $managerCount;

                    $selectedManagers->push(
                        $managers[$managerIndex]
                    );
                }

                // =====================================================
                // 6. إنشاء سجلات المستلمين
                // =====================================================

                foreach (
                    $selectedManagers as $recipientIndex => $manager
                ) {

                    // -------------------------------------------------
                    // تحديد حالة القراءة
                    // -------------------------------------------------

                    /*
                     * نجعل بعض الرسائل مقروءة وبعضها غير مقروءة
                     * حتى تكون بيانات الـ Dashboard واقعية.
                     */

                    $isRead = (
                            ($index + $recipientIndex) % 3
                        ) !== 0;

                    $readAt = null;

                    if ($isRead) {

                        $readAt = $sentAt
                            ->copy()
                            ->addHours(
                                (($index + $recipientIndex) % 12) + 1
                            );

                        /*
                         * عدم السماح بأن يكون read_at في المستقبل.
                         */

                        if (
                            $readAt->greaterThan(
                                Carbon::now()
                            )
                        ) {
                            $readAt = Carbon::now();
                        }
                    }

                    // -------------------------------------------------
                    // البحث عن سجل المستلم
                    // -------------------------------------------------

                    $existingRecipient = DB::table(
                        'tameem_recipients'
                    )
                        ->where(
                            'tameem_id',
                            $tameemId
                        )
                        ->where(
                            'mosque_manager_id',
                            $manager->id
                        )
                        ->first();

                    if ($existingRecipient) {

                        DB::table('tameem_recipients')
                            ->where(
                                'id',
                                $existingRecipient->id
                            )
                            ->update([
                                /*
                                 * مهم جداً مع PostgreSQL:
                                 * نرسل Boolean حقيقي وليس 1/0.
                                 */
                                'is_read' => (bool) $isRead,

                                'read_at' => $readAt,

                                'updated_at' => now(),
                            ]);

                    } else {

                        DB::table('tameem_recipients')
                            ->insert([
                                'tameem_id' => $tameemId,

                                'mosque_manager_id' =>
                                    $manager->id,

                                /*
                                 * Boolean حقيقي
                                 */
                                'is_read' => (bool) $isRead,

                                'read_at' => $readAt,

                                'created_at' => now(),

                                'updated_at' => now(),
                            ]);

                        $totalRecipients++;
                    }
                }
            }

            // =========================================================
            // 7. Commit
            // =========================================================

            DB::commit();

            // =========================================================
            // 8. Summary
            // =========================================================

            $this->command->info('');

            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            $this->command->info(
                '✅ تم تنفيذ TameemSeeder بنجاح.'
            );

            $this->command->info(
                '🕌 المساجد: ' .
                $mosques->count()
            );

            $this->command->info(
                '👤 مديرو المساجد: ' .
                $managers->count()
            );

            $this->command->info(
                '📢 التعاميم: ' .
                $totalTameems
            );

            $this->command->info(
                '📨 سجلات المستلمين الجديدة: ' .
                $totalRecipients
            );

            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error('');

            $this->command->error(
                '❌ حدث خطأ أثناء إنشاء التعاميم.'
            );

            $this->command->error(
                '📍 الرسالة: ' .
                $e->getMessage()
            );

            $this->command->error(
                '📁 الملف: ' .
                $e->getFile()
            );

            $this->command->error(
                '📍 السطر: ' .
                $e->getLine()
            );

            throw $e;
        }
    }
}
