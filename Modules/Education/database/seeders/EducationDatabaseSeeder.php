<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Modules\User\Models\TeacherProfile;
use Modules\User\Models\User;
use Modules\User\Models\Role;
use Modules\Mosque\Models\Mosque;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation;
use Modules\Education\Models\AttendanceExcuse;

class EducationDatabaseSeeder extends Seeder
{
    /*
    |--------------------------------------------------------------------------
    | إعدادات Seeder
    |--------------------------------------------------------------------------
    */

    /**
     * عدد الحلقات في كل مسجد.
     */
    private int $halaqatPerMosque = 3;

    /**
     * عدد الطلاب الإجمالي.
     */
    private int $studentsCount = 200;

    /**
     * أسماء المعلمين الذين سيتم إنشاؤهم عند الحاجة.
     */
    private array $teacherFirstNames = [
        'محمد',
        'أحمد',
        'علي',
        'حسن',
        'حسين',
        'إبراهيم',
        'موسى',
        'عمر',
        'ياسر',
        'خالد',
        'عبد الله',
        'عبد الرحمن',
        'عبد العزيز',
        'يوسف',
        'حمزة',
        'بلال',
        'سعد',
        'سلمان',
        'معاذ',
        'أنس',
        'زياد',
        'رامي',
        'مازن',
        'طارق',
        'كريم',
        'مصطفى',
        'أيمن',
        'سامر',
        'باسل',
        'فراس',
        'طارق',
        'محمود',
        'وليد',
        'هشام',
        'رامز',
    ];

    private array $firstNames = [
        'محمد',
        'أحمد',
        'علي',
        'حسن',
        'حسين',
        'إبراهيم',
        'موسى',
        'عمر',
        'ياسر',
        'خالد',
        'عبد الله',
        'عبد الرحمن',
        'عبد العزيز',
        'يوسف',
        'حمزة',
        'بلال',
        'سعد',
        'سلمان',
        'معاذ',
        'أنس',
        'زياد',
        'رامي',
        'مازن',
        'طارق',
        'كريم',
        'مصطفى',
        'أيمن',
        'سامر',
        'باسل',
        'فراس',
    ];

    private array $lastNames = [
        'محمود',
        'علي',
        'الشامي',
        'الخطيب',
        'الحموي',
        'الدمشقي',
        'الحلبي',
        'العمر',
        'الحسن',
        'النعسان',
        'الرفاعي',
        'القادري',
        'القرشي',
        'المالكي',
        'الحسيني',
    ];

    private array $specializations = [
        'عاصم عن حفص والتجويد المتقدم',
        'قراءات العشر والأساسيات',
        'تجويد وتحفيظ أجزاء عم وتبارك',
        'مراجعة وتثبيت القرآن كامل',
    ];

    private array $surahs = [
        'الفاتحة',
        'البقرة',
        'آل عمران',
        'النساء',
        'المائدة',
        'الأنعام',
        'الأعراف',
        'الأنفال',
        'التوبة',
        'يونس',
        'هود',
        'يوسف',
        'الرعد',
        'إبراهيم',
        'الحجر',
        'النحل',
        'الإسراء',
        'الكهف',
    ];

    private array $evaluationNotes = [
        'ممتاز جداً',
        'متميز',
        'جيد جداً',
        'جيد',
        'مقبول',
        'يحتاج تحسين',
        'بحاجة لمتابعة',
        'متوسط الأداء',
        'متقدم',
    ];

    private array $excuseReasons = [
        'وعكة صحية',
        'ظرف عائلي طارئ',
        'مرض مفاجئ',
        'سفر ضروري',
        'موعد طبي',
        'ظروف قاهرة',
    ];

    private array $schedules = [
        ['saturday', 'monday', 'wednesday'],
        ['sunday', 'tuesday', 'thursday'],
        ['monday', 'wednesday', 'friday'],
    ];

    /*
    |--------------------------------------------------------------------------
    | RUN
    |--------------------------------------------------------------------------
    */

    public function run(): void
    {
        DB::beginTransaction();

        try {

            $this->command->info('');
            $this->command->info('🚀 بدء إنشاء بيانات Education...');
            $this->command->info('');

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

            $mosqueIds = $mosques
                ->pluck('id')
                ->values()
                ->toArray();

            $this->command->info(
                '🕌 عدد المساجد: ' . count($mosqueIds)
            );

            // =========================================================
            // 2. جلب Roles
            // =========================================================

            $parentRole = Role::where('name', 'parent')->first();

            if (!$parentRole) {
                throw new \Exception(
                    '❌ Role "parent" غير موجود. شغّل RoleSeeder أولاً.'
                );
            }

            $teacherRole = Role::where('name', 'teacher')->first();

            if (!$teacherRole) {
                throw new \Exception(
                    '❌ Role "teacher" غير موجود. شغّل RoleSeeder أولاً.'
                );
            }

            // =========================================================
            // 3. جلب المعلمين الموجودين
            // =========================================================

            $teachers = User::whereHas('roles', function ($query) use ($teacherRole) {
                $query->where('roles.id', $teacherRole->id);
            })
                ->orderBy('id')
                ->get();

            $requiredTeachers =
                count($mosqueIds) * $this->halaqatPerMosque;

            $this->command->info(
                "👨‍🏫 المعلمون الموجودون: {$teachers->count()}"
            );

            $this->command->info(
                "🎯 المعلمون المطلوبون: {$requiredTeachers}"
            );

            // =========================================================
            // 4. إنشاء المعلمين الناقصين
            // =========================================================

            if ($teachers->count() < $requiredTeachers) {

                $missingTeachers =
                    $requiredTeachers - $teachers->count();

                $this->command->warn(
                    "⚠️ يوجد نقص في المعلمين: {$missingTeachers}"
                );

                $this->command->info(
                    '🔄 سيتم إنشاء المعلمين الناقصين تلقائياً...'
                );

                for ($i = 0; $i < $missingTeachers; $i++) {

                    $teacherNumber =
                        $teachers->count() + $i + 1;

                    $firstName = $this->teacherFirstNames[
                    $i % count($this->teacherFirstNames)
                    ];

                    $lastName = $this->lastNames[
                    $i % count($this->lastNames)
                    ];

                    $email =
                        'teacher' .
                        $teacherNumber .
                        '@mms.test';

                    /*
                     * إذا كان المستخدم موجوداً مسبقاً
                     * لا ننشئه مرة ثانية.
                     */
                    $teacher = User::firstOrCreate(
                        [
                            'email' => $email,
                        ],
                        [
                            'name' =>
                                $firstName .
                                ' ' .
                                $lastName,

                            'password' => Hash::make(
                                'password'
                            ),

                            'phone' =>
                                '+9639' .
                                str_pad(
                                    (string) $teacherNumber,
                                    8,
                                    '0',
                                    STR_PAD_LEFT
                                ),

                            'mosque_id' =>
                                $mosqueIds[
                                $i % count($mosqueIds)
                                ],
                        ]
                    );

                    /*
                     * ربط المعلم بدور teacher.
                     *
                     * إذا كان لديك Spatie Permission
                     * نستخدم assignRole.
                     */
                    if (method_exists($teacher, 'assignRole')) {

                        if (!$teacher->hasRole('teacher')) {
                            $teacher->assignRole('teacher');
                        }

                    } else {

                        /*
                         * fallback إذا كانت العلاقة
                         * مخصصة في المشروع.
                         */
                        $teacher->roles()->syncWithoutDetaching([
                            $teacherRole->id,
                        ]);
                    }

                    /*
                     * تحديث mosque_id إذا لم يكن موجوداً.
                     */
                    if (!$teacher->mosque_id) {

                        $teacher->update([
                            'mosque_id' =>
                                $mosqueIds[
                                $i % count($mosqueIds)
                                ],
                        ]);
                    }

                    $teachers->push($teacher);

                    $this->command->info(
                        "  ➜ تم تجهيز المعلم: {$teacher->name}"
                    );
                }
            }

            // =========================================================
            // 5. التحقق النهائي من عدد المعلمين
            // =========================================================

            if ($teachers->count() < $requiredTeachers) {

                throw new \Exception(
                    "❌ فشل تجهيز المعلمين. " .
                    "المطلوب {$requiredTeachers}، " .
                    "والموجود {$teachers->count()}."
                );
            }

            $this->command->info(
                "✅ أصبح لدينا {$teachers->count()} معلم."
            );

            // =========================================================
            // 6. جلب أولياء الأمور
            // =========================================================

            $parents = User::whereHas('roles', function ($query) use ($parentRole) {
                $query->where('roles.id', $parentRole->id);
            })
                ->orderBy('id')
                ->get();

            if ($parents->isEmpty()) {

                throw new \Exception(
                    '❌ لا يوجد مستخدمون بدور parent. شغّل UserSeeder أولاً.'
                );
            }

            $this->command->info(
                '👨‍👩‍👧 أولياء الأمور: ' .
                $parents->count()
            );

            // =========================================================
            // 7. Teacher Profiles
            // =========================================================

            foreach ($teachers as $teacher) {

                TeacherProfile::updateOrCreate(
                    [
                        'user_id' => $teacher->id,
                    ],
                    [
                        'phone' =>
                            $teacher->phone
                            ?? '+9639' . rand(
                                10000000,
                                99999999
                            ),

                        'specialization' =>
                            collect(
                                $this->specializations
                            )->random(),

                        'status' => 'active',

                        'notes' =>
                            'معلم قرآن كريم وحلقة تحفيظ.',
                    ]
                );
            }

            $this->command->info(
                '✅ تم تجهيز Teacher Profiles.'
            );

            // =========================================================
            // 8. إنشاء الحلقات
            // =========================================================

            $halaqat = collect();

            $teacherIndex = 0;
            $halaqaCounter = 1;

            $startTimes = [
                '16:00',
                '17:30',
                '18:00',
                '19:30',
            ];

            foreach ($mosques as $mosque) {

                $this->command->info('');

                $this->command->info(
                    "🕌 المسجد #{$mosque->id}: إنشاء {$this->halaqatPerMosque} حلقات..."
                );

                for (
                    $halaqaNumber = 1;
                    $halaqaNumber <= $this->halaqatPerMosque;
                    $halaqaNumber++
                ) {

                    $teacher = $teachers[$teacherIndex];

                    /*
                     * ربط المعلم بالمسجد.
                     */
                    if ($teacher->mosque_id != $mosque->id) {

                        $teacher->update([
                            'mosque_id' => $mosque->id,
                        ]);
                    }

                    $startTime =
                        $startTimes[
                        ($halaqaNumber - 1) %
                        count($startTimes)
                        ];

                    $endTime = Carbon::createFromFormat(
                        'H:i',
                        $startTime
                    )
                        ->addMinutes(90)
                        ->format('H:i:s');

                    $schedule =
                        $this->schedules[
                        ($halaqaNumber - 1) %
                        count($this->schedules)
                        ];

                    $name =
                        'حلقة ' .
                        $halaqaNumber .
                        ' - ' .
                        $mosque->id .
                        ' - ' .
                        $teacher->name;

                    $halaqa = Halaqa::firstOrCreate(
                        [
                            'teacher_id' => $teacher->id,
                            'mosque_id' => $mosque->id,
                            'name' => $name,
                        ],
                        [
                            'capacity' => rand(15, 30),

                            'schedule_days' => $schedule,

                            'start_time' => $startTime,

                            'end_time' => $endTime,

                            'status' => 'active',
                        ]
                    );

                    $halaqat->push($halaqa);

                    $this->command->info(
                        "  📖 {$name} → المعلم {$teacher->id}"
                    );

                    $teacherIndex++;
                    $halaqaCounter++;
                }
            }

            $this->command->info('');

            $this->command->info(
                '📖 إجمالي الحلقات: ' .
                $halaqat->count()
            );

            // =========================================================
            // 9. إنشاء الطلاب
            // =========================================================

            $studentHalaqaMap = [];

            $studentCount = 0;

            $this->command->info('');

            $this->command->info(
                "👦 إنشاء {$this->studentsCount} طالب..."
            );

            for (
                $i = 1;
                $i <= $this->studentsCount;
                $i++
            ) {

                if ($halaqat->isEmpty()) {
                    break;
                }

                /*
                 * توزيع الطلاب على جميع الحلقات.
                 */
                $halaqa =
                    $halaqat[
                    ($i - 1) %
                    $halaqat->count()
                    ];

                /*
                 * توزيع أولياء الأمور.
                 */
                $parent =
                    $parents[
                    ($i - 1) %
                    $parents->count()
                    ];

                $student = Student::create([
                    'first_name' =>
                        collect(
                            $this->firstNames
                        )->random(),

                    'last_name' =>
                        collect(
                            $this->lastNames
                        )->random(),

                    'parent_id' =>
                        $parent->id,

                    'mosque_id' =>
                        $halaqa->mosque_id,

                    'date_of_birth' =>
                        now()
                            ->subYears(
                                rand(7, 15)
                            )
                            ->toDateString(),

                    'gender' => 'male',

                    'status' => 'active',

                    'halaqa_id' =>
                        $halaqa->id,
                ]);

                $studentHalaqaMap[
                $student->id
                ] = $halaqa->id;

                $studentCount++;

                if ($studentCount % 50 === 0) {

                    $this->command->info(
                        "  ➜ تم إنشاء {$studentCount} طالب..."
                    );
                }
            }

            $this->command->info(
                "👦 تم إنشاء {$studentCount} طالب."
            );

            // =========================================================
            // 10. Attendance Excuses
            // =========================================================

            $this->command->info(
                '📋 إنشاء أعذار الغياب...'
            );

            $studentsForExcuses = Student::query()
                ->whereIn(
                    'id',
                    array_keys($studentHalaqaMap)
                )
                ->inRandomOrder()
                ->take(
                    min(
                        20,
                        count($studentHalaqaMap)
                    )
                )
                ->get();

            foreach ($studentsForExcuses as $student) {

                AttendanceExcuse::create([
                    'student_id' =>
                        $student->id,

                    'halaqa_id' =>
                        $student->halaqa_id,

                    'parent_id' =>
                        $student->parent_id,

                    'absence_date' =>
                        now()
                            ->subDays(
                                rand(1, 10)
                            )
                            ->toDateString(),

                    'reason' =>
                        collect(
                            $this->excuseReasons
                        )->random(),

                    'status' => 'pending',

                    'admin_comment' => null,
                ]);
            }

            $this->command->info(
                '✅ تم إنشاء أعذار الغياب.'
            );

            // =========================================================
            // 11. Attendance + Evaluations
            // =========================================================

            $this->command->info('');

            $this->command->info(
                '🔄 جاري إنشاء الحضور والتقييمات...'
            );

            $attendancesData = [];
            $evaluationsData = [];

            $nowStr =
                Carbon::now()->toDateTimeString();

            foreach (
                $studentHalaqaMap
                as $studentId => $halaqaId
            ) {

                $halaqa =
                    $halaqat->firstWhere(
                        'id',
                        $halaqaId
                    );

                if (!$halaqa) {
                    continue;
                }

                // =====================================================
                // Attendance
                // =====================================================

                for (
                    $day = 30;
                    $day >= 0;
                    $day--
                ) {

                    $date =
                        Carbon::today()
                            ->subDays($day);

                    $dayName =
                        strtolower(
                            $date->format('l')
                        );

                    if (
                        !in_array(
                            $dayName,
                            $halaqa->schedule_days ?? []
                        )
                    ) {
                        continue;
                    }

                    $status = collect([
                        'present',
                        'present',
                        'present',
                        'late',
                        'absent',
                    ])->random();

                    $attendancesData[] = [
                        'halaqa_id' =>
                            $halaqaId,

                        'student_id' =>
                            $studentId,

                        'date' =>
                            $date->toDateString(),

                        'status' =>
                            $status,

                        'notes' => null,

                        'created_at' =>
                            $nowStr,

                        'updated_at' =>
                            $nowStr,
                    ];
                }

                // =====================================================
                // Evaluations
                // =====================================================

                $evalCount =
                    rand(5, 15);

                for (
                    $i = 0;
                    $i < $evalCount;
                    $i++
                ) {

                    $from =
                        rand(1, 100);

                    $to =
                        min(
                            $from + rand(2, 15),
                            286
                        );

                    $evaluationsData[] = [
                        'client_uuid' =>
                            Str::uuid()->toString(),

                        'halaqa_id' =>
                            $halaqaId,

                        'student_id' =>
                            $studentId,

                        'surah_name' =>
                            collect(
                                $this->surahs
                            )->random(),

                        'from_ayah' =>
                            $from,

                        'to_ayah' =>
                            $to,

                        'score' =>
                            collect([
                                75,
                                80,
                                85,
                                90,
                                95,
                                100,
                            ])->random(),

                        'notes' =>
                            collect(
                                $this->evaluationNotes
                            )->random(),

                        'evaluated_at' =>
                            now()
                                ->subDays(
                                    rand(1, 30)
                                )
                                ->toDateString(),

                        'created_at' =>
                            $nowStr,

                        'updated_at' =>
                            $nowStr,
                    ];
                }
            }

            // =========================================================
            // 12. Bulk Attendance
            // =========================================================

            $this->command->info(
                '⚡ إدخال ' .
                count($attendancesData) .
                ' سجل حضور...'
            );

            foreach (
                array_chunk(
                    $attendancesData,
                    500
                ) as $chunk
            ) {

                Attendance::insert($chunk);
            }

            // =========================================================
            // 13. Bulk Evaluations
            // =========================================================

            $this->command->info(
                '⚡ إدخال ' .
                count($evaluationsData) .
                ' سجل تقييم...'
            );

            foreach (
                array_chunk(
                    $evaluationsData,
                    500
                ) as $chunk
            ) {

                Evaluation::insert($chunk);
            }

            // =========================================================
            // 14. Commit
            // =========================================================

            DB::commit();

            $this->command->info('');

            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            $this->command->info(
                '🎉 تم تنفيذ EducationDatabaseSeeder بنجاح!'
            );

            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            $this->printSummary(
                $studentCount,
                $halaqat->count(),
                $teachers->count(),
                count($attendancesData),
                count($evaluationsData)
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error('');

            $this->command->error(
                '❌ حدث خطأ أثناء تنفيذ EducationDatabaseSeeder'
            );

            $this->command->error(
                '📍 السطر: ' .
                $e->getLine()
            );

            $this->command->error(
                '📁 الملف: ' .
                $e->getFile()
            );

            $this->command->error(
                '💬 الرسالة: ' .
                $e->getMessage()
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    private function printSummary(
        int $students,
        int $halaqat,
        int $teachers,
        int $attendance,
        int $evaluations
    ): void {

        $this->command->info('');

        $this->command->info(
            '📊 ملخص بيانات Education'
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info(
            '🕌 المساجد: ' .
            Mosque::count()
        );

        $this->command->info(
            '👨‍🏫 المعلمون: ' .
            $teachers
        );

        $this->command->info(
            '📖 الحلقات: ' .
            $halaqat
        );

        $this->command->info(
            '👦 الطلاب: ' .
            $students
        );

        $this->command->info(
            '📝 سجلات الحضور: ' .
            $attendance
        );

        $this->command->info(
            '⭐ التقييمات: ' .
            $evaluations
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );

        $this->command->info('');
    }
}
