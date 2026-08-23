<?php

namespace Modules\Complaint\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Modules\Complaint\Models\Complaint;
use Modules\Complaint\Models\ComplaintStatusLog;
use Modules\Complaint\Models\ComplaintFile;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class ComplaintDatabaseSeeder extends Seeder
{
    /**
     * عدد الشكاوى المطلوب إنشاؤها.
     */
    private int $complaintsCount = 30;

    /**
     * بيانات الشكاوى.
     */
    private array $complaintTitles = [
        'انقطاع الكهرباء في المسجد',
        'مشكلة في إنارة المسجد',
        'الحاجة إلى صيانة دورات المياه',
        'مشكلة في أجهزة التكييف',
        'نقص في مستلزمات النظافة',
        'مشكلة في نظام الصوت',
        'الحاجة إلى صيانة الأبواب',
        'مشكلة في شبكة المياه',
        'الحاجة إلى صيانة الإنارة الخارجية',
        'مشكلة تقنية في أجهزة المسجد',
        'عدم توفر بعض الخدمات',
        'الحاجة إلى صيانة فرش المسجد',
        'مشكلة في التهوية',
        'الحاجة إلى تنظيف شامل',
        'عطل في أجهزة الصوت',
    ];

    /**
     * أنواع الشكاوى.
     */
    private array $complaintTypes = [
        'service_missing',
        'power_outage',
        'corruption',
        'employee_misconduct',
        'technical_issue',
    ];

    /**
     * الأولويات.
     */
    private array $priorities = [
        'low',
        'medium',
        'medium',
        'medium',
        'high',
    ];

    /**
     * الحالات.
     */
    private array $statuses = [
        'pending',
        'pending',
        'in_progress',
        'in_progress',
        'resolved',
        'canceled',
    ];

    /**
     * أسباب / أوصاف الشكاوى.
     */
    private array $descriptions = [
        'تم تسجيل شكوى بخصوص وجود مشكلة تحتاج إلى معالجة من قبل الإدارة المختصة.',
        'يوجد خلل في الخدمة داخل المسجد ونرجو اتخاذ الإجراء المناسب.',
        'نرجو معالجة المشكلة في أقرب وقت ممكن لضمان استمرار الخدمة بشكل جيد.',
        'تمت ملاحظة المشكلة من قبل أحد المستفيدين ويحتاج الأمر إلى متابعة.',
        'المشكلة تؤثر على مستوى الخدمة المقدمة في المسجد.',
        'نرجو إرسال فريق مختص للكشف عن المشكلة ومعالجتها.',
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {

            $this->command->info('');
            $this->command->info('🚀 بدء إنشاء بيانات الشكاوى...');
            $this->command->info('');

            // =========================================================
            // 1. التحقق من وجود المسجد رقم 1
            // =========================================================

            $mosque = Mosque::find(1);

            if (!$mosque) {
                throw new \Exception(
                    '❌ المسجد رقم 1 غير موجود في قاعدة البيانات.'
                );
            }

            $this->command->info(
                "🕌 سيتم ربط جميع الشكاوى بالمسجد: {$mosque->id}"
            );

            // =========================================================
            // 2. جلب المستخدمين الموجودين
            // =========================================================

            $users = User::query()
                ->orderBy('id')
                ->get();

            if ($users->isEmpty()) {
                throw new \Exception(
                    '❌ لا يوجد مستخدمون في قاعدة البيانات.'
                );
            }

            $this->command->info(
                "👤 عدد المستخدمين المتاحين: {$users->count()}"
            );

            // =========================================================
            // 3. حذف الشكاوى القديمة الخاصة بالمسجد 1
            // =========================================================

            $this->clearMosqueComplaints($mosque->id);

            // =========================================================
            // 4. إنشاء الشكاوى
            // =========================================================

            $this->command->info('');
            $this->command->info(
                "📝 إنشاء {$this->complaintsCount} شكوى..."
            );

            $complaintsCount = 0;

            for ($i = 1; $i <= $this->complaintsCount; $i++) {

                // -----------------------------------------------------
                // المستخدم صاحب الشكوى
                // -----------------------------------------------------

                $isAnonymous = $i % 5 === 0;

                $user = $users[
                ($i - 1) % $users->count()
                ];

                // -----------------------------------------------------
                // الحالة
                // -----------------------------------------------------

                $status = $this->statuses[
                ($i - 1) % count($this->statuses)
                ];

                // -----------------------------------------------------
                // الأولوية
                // -----------------------------------------------------

                $priority = $this->priorities[
                ($i - 1) % count($this->priorities)
                ];

                // -----------------------------------------------------
                // نوع الشكوى
                // -----------------------------------------------------

                $complaintType = $this->complaintTypes[
                ($i - 1) % count($this->complaintTypes)
                ];

                // -----------------------------------------------------
                // رقم الشكوى
                // -----------------------------------------------------

                $complaintNumber =
                    'CMP-' .
                    now()->format('Ymd') .
                    '-' .
                    str_pad(
                        (string) $i,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );

                // -----------------------------------------------------
                // إنشاء الشكوى
                // -----------------------------------------------------

                $complaint = Complaint::create([
                    'complaint_number' =>
                        $complaintNumber,

                    'title' =>
                        $this->complaintTitles[
                        ($i - 1) %
                        count($this->complaintTitles)
                        ],

                    'description' =>
                        $this->descriptions[
                        ($i - 1) %
                        count($this->descriptions)
                        ],

                    'status' =>
                        $status,

                    'priority' =>
                        $priority,

                    'complaint_type' =>
                        $complaintType,

                    'email' =>
                        $isAnonymous
                            ? null
                            : $user->email,



                    'admin_notes' =>
                        $status === 'resolved'
                            ? 'تمت معالجة الشكوى وإغلاقها.'
                            : null,

                    'user_id' =>
                        $isAnonymous
                            ? null
                            : $user->id,

                    'mosque_id' =>
                        $mosque->id,

                    'assigned_admin_id' =>
                        $this->getAdminId($users),
                ]);

                $complaintsCount++;

                // =====================================================
                // إنشاء Status Log
                // =====================================================

                $this->createStatusLogs(
                    $complaint,
                    $status,
                    $users
                );

                // =====================================================
                // إنشاء ملف مرفق لبعض الشكاوى
                // =====================================================

                if ($i % 4 === 0) {

                    ComplaintFile::create([
                        'complaint_id' =>
                            $complaint->id,

                        'file' =>
                            'complaints/example-' .
                            $complaint->id .
                            '.pdf',

                        'file_type' =>
                            'application/pdf',
                    ]);
                }

                $this->command->info(
                    "  ➜ {$complaint->complaint_number} | " .
                    "{$complaint->title} | " .
                    "{$status}"
                );
            }

            // =========================================================
            // 5. Commit
            // =========================================================

            DB::commit();

            $this->command->info('');
            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            $this->command->info(
                '🎉 تم إنشاء الشكاوى بنجاح!'
            );

            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            $this->printSummary(
                $mosque->id,
                $complaintsCount
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error('');
            $this->command->error(
                '❌ حدث خطأ أثناء إنشاء الشكاوى.'
            );

            $this->command->error(
                '📍 السطر: ' . $e->getLine()
            );

            $this->command->error(
                '📁 الملف: ' . $e->getFile()
            );

            $this->command->error(
                '💬 الرسالة: ' . $e->getMessage()
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | حذف الشكاوى القديمة للمسجد
    |--------------------------------------------------------------------------
    */

    private function clearMosqueComplaints(int $mosqueId): void
    {
        $this->command->warn(
            "🧹 حذف الشكاوى القديمة للمسجد #{$mosqueId}..."
        );

        $complaintIds = Complaint::query()
            ->where('mosque_id', $mosqueId)
            ->pluck('id');

        if ($complaintIds->isEmpty()) {

            $this->command->info(
                'ℹ️ لا توجد شكاوى قديمة.'
            );

            return;
        }

        // -------------------------------------------------------------
        // حذف الملفات
        // -------------------------------------------------------------

        ComplaintFile::query()
            ->whereIn(
                'complaint_id',
                $complaintIds
            )
            ->delete();

        // -------------------------------------------------------------
        // حذف سجل الحالات
        // -------------------------------------------------------------

        ComplaintStatusLog::query()
            ->whereIn(
                'complaint_id',
                $complaintIds
            )
            ->delete();

        // -------------------------------------------------------------
        // حذف الشكاوى
        // -------------------------------------------------------------

        Complaint::query()
            ->whereIn(
                'id',
                $complaintIds
            )
            ->delete();

        $this->command->info(
            "✅ تم حذف {$complaintIds->count()} شكوى قديمة."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء سجل حالات الشكوى
    |--------------------------------------------------------------------------
    */

    private function createStatusLogs(
        Complaint $complaint,
        string $status,
                  $users
    ): void {

        /*
         * نحتاج مستخدمًا لتسجيل من قام بتغيير الحالة.
         */

        $changedBy = $users[
        ($complaint->id - 1) % $users->count()
        ];

        // -------------------------------------------------------------
        // السجل الأول: إنشاء الشكوى
        // -------------------------------------------------------------

        ComplaintStatusLog::create([
            'complaint_id' =>
                $complaint->id,

            'old_status' =>
                null,

            'new_status' =>
                'pending',

            'note' =>
                'تم تسجيل الشكوى.',

            'changed_at' =>
                Carbon::now()
                    ->subDays(
                        rand(1, 20)
                    ),

            'changed_by' =>
                $changedBy->id,
        ]);

        // -------------------------------------------------------------
        // إذا كانت الشكوى قيد المعالجة
        // -------------------------------------------------------------

        if (
            in_array(
                $status,
                ['in_progress', 'resolved']
            )
        ) {

            ComplaintStatusLog::create([
                'complaint_id' =>
                    $complaint->id,

                'old_status' =>
                    'pending',

                'new_status' =>
                    'in_progress',

                'note' =>
                    'تمت إحالة الشكوى إلى الإدارة المختصة للمتابعة.',

                'changed_at' =>
                    Carbon::now()
                        ->subDays(
                            rand(1, 10)
                        ),

                'changed_by' =>
                    $changedBy->id,
            ]);
        }

        // -------------------------------------------------------------
        // إذا كانت محلولة
        // -------------------------------------------------------------

        if ($status === 'resolved') {

            ComplaintStatusLog::create([
                'complaint_id' =>
                    $complaint->id,

                'old_status' =>
                    'in_progress',

                'new_status' =>
                    'resolved',

                'note' =>
                    'تمت معالجة الشكوى وحل المشكلة.',

                'changed_at' =>
                    Carbon::now()
                        ->subDays(
                            rand(0, 5)
                        ),

                'changed_by' =>
                    $changedBy->id,
            ]);
        }

        // -------------------------------------------------------------
        // إذا كانت ملغاة
        // -------------------------------------------------------------

        if ($status === 'canceled') {

            ComplaintStatusLog::create([
                'complaint_id' =>
                    $complaint->id,

                'old_status' =>
                    'pending',

                'new_status' =>
                    'canceled',

                'note' =>
                    'تم إلغاء الشكوى.',

                'changed_at' =>
                    Carbon::now()
                        ->subDays(
                            rand(1, 10)
                        ),

                'changed_by' =>
                    $changedBy->id,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على Admin
    |--------------------------------------------------------------------------
    */

    private function getAdminId($users): ?int
    {
        /*
         * نحاول أولاً العثور على مستخدم لديه دور admin.
         */

        foreach ($users as $user) {

            if (
                method_exists($user, 'hasRole') &&
                $user->hasRole('admin')
            ) {
                return $user->id;
            }
        }

        /*
         * إذا لم يوجد admin، نترك assigned_admin_id
         * فارغاً.
         */

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    private function printSummary(
        int $mosqueId,
        int $complaints
    ): void {

        $this->command->info('');

        $this->command->info(
            '📊 ملخص الشكاوى'
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info(
            '🕌 المسجد: #' . $mosqueId
        );

        $this->command->info(
            '📝 الشكاوى: ' . $complaints
        );

        $this->command->info(
            '📋 سجلات الحالات: ' .
            ComplaintStatusLog::whereHas(
                'complaint',
                function ($query) use ($mosqueId) {
                    $query->where(
                        'mosque_id',
                        $mosqueId
                    );
                }
            )->count()
        );

        $this->command->info(
            '📎 الملفات: ' .
            ComplaintFile::whereHas(
                'complaint',
                function ($query) use ($mosqueId) {
                    $query->where(
                        'mosque_id',
                        $mosqueId
                    );
                }
            )->count()
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info('');
    }
}
