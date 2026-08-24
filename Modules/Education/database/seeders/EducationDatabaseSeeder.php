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
     * عدد الحلقات في باقي المساجد.
     */
    private int $halaqatPerMosque = 3;

    /**
     * عدد الحلقات في المسجد الأول.
     */
    private int $firstMosqueHalaqat = 7;

    /**
     * عدد الطلاب في كل حلقة من حلقات المسجد الأول.
     */
    private int $studentsPerFirstMosqueHalaqa = 20;

    /**
     * عدد الطلاب الإجمالي.
     */
    private int $studentsCount = 200;

    /**
     * الحد الأدنى لعدد أولياء الأمور.
     */
    private int $parentsCount = 30;

    /*
    |--------------------------------------------------------------------------
    | الأسماء
    |--------------------------------------------------------------------------
    */

    /**
     * أسماء المعلمين.
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
        'محمود',
        'وليد',
        'هشام',
        'رامز',
    ];

    /**
     * أسماء الطلاب وأولياء الأمور.
     */
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

    /**
     * أسماء العائلات.
     */
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

    /**
     * اختصاصات المعلمين.
     */
    private array $specializations = [
        'عاصم عن حفص والتجويد المتقدم',
        'قراءات العشر والأساسيات',
        'تجويد وتحفيظ أجزاء عم وتبارك',
        'مراجعة وتثبيت القرآن كامل',
    ];

    /**
     * أسماء السور.
     */
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

    /**
     * ملاحظات التقييم.
     */
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

    /**
     * أسباب أعذار الغياب.
     */
    private array $excuseReasons = [
        'وعكة صحية',
        'ظرف عائلي طارئ',
        'مرض مفاجئ',
        'سفر ضروري',
        'موعد طبي',
        'ظروف قاهرة',
    ];

    /**
     * جداول الحلقات.
     */
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
            // 0. حذف بيانات Education القديمة
            // =========================================================

            $this->clearEducationData();

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

            $firstMosque = $mosques->first();

            $this->command->info(
                "⭐ المسجد الأول هو المسجد رقم: {$firstMosque->id}"
            );

            // =========================================================
            // 2. جلب Roles
            // =========================================================

            $parentRole = Role::where(
                'name',
                'parent'
            )->first();

            if (!$parentRole) {
                throw new \Exception(
                    '❌ Role "parent" غير موجود. شغّل RoleSeeder أولاً.'
                );
            }

            $teacherRole = Role::where(
                'name',
                'teacher'
            )->first();

            if (!$teacherRole) {
                throw new \Exception(
                    '❌ Role "teacher" غير موجود. شغّل RoleSeeder أولاً.'
                );
            }

            // =========================================================
            // 3. حساب عدد المعلمين المطلوب
            // =========================================================

            /*
             * المسجد الأول:
             * 7 معلمين
             *
             * باقي المساجد:
             * 3 معلمين لكل مسجد
             */

            $requiredTeachers =
                $this->firstMosqueHalaqat +
                (
                    (count($mosqueIds) - 1)
                    *
                    $this->halaqatPerMosque
                );

            // =========================================================
            // 4. جلب المعلمين
            // =========================================================

            $teachers = User::whereHas(
                'roles',
                function ($query) use ($teacherRole) {
                    $query->where(
                        'roles.id',
                        $teacherRole->id
                    );
                }
            )
                ->orderBy('id')
                ->get();

            $this->command->info(
                "👨‍🏫 المعلمون الموجودون: {$teachers->count()}"
            );

            $this->command->info(
                "🎯 المعلمون المطلوبون: {$requiredTeachers}"
            );

            // =========================================================
            // 5. إنشاء المعلمين الناقصين
            // =========================================================

            if ($teachers->count() < $requiredTeachers) {

                $missingTeachers =
                    $requiredTeachers -
                    $teachers->count();

                $this->command->warn(
                    "⚠️ يوجد نقص في المعلمين: {$missingTeachers}"
                );

                $this->command->info(
                    '🔄 سيتم إنشاء المعلمين الناقصين تلقائياً...'
                );

                for (
                    $i = 0;
                    $i < $missingTeachers;
                    $i++
                ) {

                    /*
                     * نستخدم العدد الحالي + 1
                     * لضمان عدم تكرار البريد.
                     */
                    $teacherNumber =
                        $teachers->count() +
                        $i +
                        1;

                    $firstName =
                        $this->teacherFirstNames[
                        $i %
                        count(
                            $this->teacherFirstNames
                        )
                        ];

                    $lastName =
                        $this->lastNames[
                        $i %
                        count(
                            $this->lastNames
                        )
                        ];

                    $email =
                        'teacher' .
                        $teacherNumber .
                        '@mms.test';

                    $teacher =
                        User::firstOrCreate(
                            [
                                'email' =>
                                    $email,
                            ],
                            [
                                'name' =>
                                    $firstName .
                                    ' ' .
                                    $lastName,

                                'password' =>
                                    Hash::make(
                                        'password'
                                    ),

                                'phone' =>
                                    '+9639' .
                                    str_pad(
                                        (string)
                                        $teacherNumber,
                                        8,
                                        '0',
                                        STR_PAD_LEFT
                                    ),

                                /*
                                 * المسجد الأول مؤقتاً.
                                 * سيتم ضبط المسجد عند إنشاء الحلقات.
                                 */
                                'mosque_id' =>
                                    $firstMosque->id,
                            ]
                        );

                    // =================================================
                    // ربط المعلم بدور teacher
                    // =================================================

                    if (
                        method_exists(
                            $teacher,
                            'assignRole'
                        )
                    ) {

                        if (
                            !$teacher->hasRole(
                                'teacher'
                            )
                        ) {

                            $teacher->assignRole(
                                'teacher'
                            );
                        }

                    } else {

                        $teacher
                            ->roles()
                            ->syncWithoutDetaching([
                                $teacherRole->id,
                            ]);
                    }

                    $teachers->push(
                        $teacher
                    );

                    $this->command->info(
                        "  ➜ تم تجهيز المعلم: {$teacher->name}"
                    );
                }
            }

            // =========================================================
            // 6. التحقق من المعلمين
            // =========================================================

            if (
                $teachers->count() <
                $requiredTeachers
            ) {

                throw new \Exception(
                    "❌ فشل تجهيز المعلمين. " .
                    "المطلوب {$requiredTeachers}، " .
                    "والموجود {$teachers->count()}."
                );
            }

            /*
             * نستخدم فقط العدد المطلوب.
             */
            $teachers =
                $teachers
                    ->take($requiredTeachers)
                    ->values();

            $this->command->info(
                "✅ تم تجهيز {$teachers->count()} معلم."
            );

            // =========================================================
            // 7. تجهيز أولياء الأمور
            // =========================================================

            $parents = User::whereHas(
                'roles',
                function ($query) use ($parentRole) {
                    $query->where(
                        'roles.id',
                        $parentRole->id
                    );
                }
            )
                ->orderBy('id')
                ->get();

            $this->command->info(
                '👨‍👩‍👧 أولياء الأمور الموجودون: ' .
                $parents->count()
            );

            // =========================================================
            // إنشاء آباء إضافيين
            // =========================================================

            if (
                $parents->count() <
                $this->parentsCount
            ) {

                $missingParents =
                    $this->parentsCount -
                    $parents->count();

                $this->command->info(
                    "➕ سيتم إنشاء {$missingParents} ولي أمر إضافي..."
                );

                for (
                    $i = 0;
                    $i < $missingParents;
                    $i++
                ) {

                    $parentNumber =
                        $parents->count() +
                        $i +
                        1;

                    $firstName =
                        $this->firstNames[
                        $i %
                        count(
                            $this->firstNames
                        )
                        ];

                    $lastName =
                        $this->lastNames[
                        $i %
                        count(
                            $this->lastNames
                        )
                        ];

                    $email =
                        'parent' .
                        $parentNumber .
                        '@mms.test';

                    $parent =
                        User::firstOrCreate(
                            [
                                'email' =>
                                    $email,
                            ],
                            [
                                'name' =>
                                    $firstName .
                                    ' ' .
                                    $lastName,

                                'password' =>
                                    Hash::make(
                                        'password'
                                    ),

                                'phone' =>
                                    '+9639' .
                                    str_pad(
                                        (string)
                                        $parentNumber,
                                        8,
                                        '0',
                                        STR_PAD_LEFT
                                    ),
                            ]
                        );

                    // =================================================
                    // ربط ولي الأمر بدور parent
                    // =================================================

                    if (
                        method_exists(
                            $parent,
                            'assignRole'
                        )
                    ) {

                        if (
                            !$parent->hasRole(
                                'parent'
                            )
                        ) {

                            $parent->assignRole(
                                'parent'
                            );
                        }

                    } else {

                        $parent
                            ->roles()
                            ->syncWithoutDetaching([
                                $parentRole->id,
                            ]);
                    }

                    $parents->push(
                        $parent
                    );

                    $this->command->info(
                        "  ➜ تم تجهيز ولي الأمر: {$parent->name}"
                    );
                }
            }

            if ($parents->isEmpty()) {

                throw new \Exception(
                    '❌ لم يتم تجهيز أي ولي أمر.'
                );
            }

            $this->command->info(
                '👨‍👩‍👧 إجمالي أولياء الأمور: ' .
                $parents->count()
            );

            // =========================================================
            // 8. Teacher Profiles
            // =========================================================

            foreach ($teachers as $teacher) {

                TeacherProfile::updateOrCreate(
                    [
                        'user_id' =>
                            $teacher->id,
                    ],
                    [
                        'phone' =>
                            $teacher->phone
                            ??
                            '+9639' .
                            rand(
                                10000000,
                                99999999
                            ),

                        'specialization' =>
                            collect(
                                $this->specializations
                            )->random(),

                        'status' =>
                            'active',

                        'notes' =>
                            'معلم قرآن كريم وحلقة تحفيظ.',
                    ]
                );
            }

            $this->command->info(
                '✅ تم تجهيز Teacher Profiles.'
            );

            // =========================================================
            // 9. إنشاء الحلقات
            // =========================================================

            $halaqat = collect();

            $teacherIndex = 0;

            $startTimes = [
                '16:00',
                '17:30',
                '18:00',
                '19:30',
                '20:00',
                '16:30',
                '18:30',
            ];

            foreach (
                $mosques as $mosqueIndex => $mosque
            ) {

                $halaqatCount =
                    $mosqueIndex === 0
                        ? $this->firstMosqueHalaqat
                        : $this->halaqatPerMosque;

                $this->command->info('');

                $this->command->info(
                    "🕌 المسجد #{$mosque->id}: إنشاء {$halaqatCount} حلقات..."
                );

                for (
                    $halaqaNumber = 1;
                    $halaqaNumber <= $halaqatCount;
                    $halaqaNumber++
                ) {

                    if (
                        !isset(
                            $teachers[$teacherIndex]
                        )
                    ) {

                        throw new \Exception(
                            '❌ لا يوجد معلم كافٍ لإنشاء الحلقات.'
                        );
                    }

                    $teacher =
                        $teachers[
                        $teacherIndex
                        ];

                    // =================================================
                    // ربط المعلم بالمسجد
                    // =================================================

                    if (
                        $teacher->mosque_id !=
                        $mosque->id
                    ) {

                        $teacher->update([
                            'mosque_id' =>
                                $mosque->id,
                        ]);
                    }

                    // =================================================
                    // وقت الحلقة
                    // =================================================

                    $startTime =
                        $startTimes[
                        ($halaqaNumber - 1)
                        %
                        count(
                            $startTimes
                        )
                        ];

                    $endTime =
                        Carbon::createFromFormat(
                            'H:i',
                            $startTime
                        )
                            ->addMinutes(90)
                            ->format(
                                'H:i:s'
                            );

                    // =================================================
                    // أيام الحلقة
                    // =================================================

                    $schedule =
                        $this->schedules[
                        ($halaqaNumber - 1)
                        %
                        count(
                            $this->schedules
                        )
                        ];

                    // =================================================
                    // اسم الحلقة
                    // =================================================

                    $name =
                        'حلقة ' .
                        $halaqaNumber .
                        ' - ' .
                        $mosque->id .
                        ' - ' .
                        $teacher->name;

                    // =================================================
                    // السعة
                    // =================================================

                    $capacity =
                        $mosqueIndex === 0
                            ? 20
                            : rand(15, 30);

                    // =================================================
                    // إنشاء الحلقة
                    // =================================================

                    $halaqa =
                        Halaqa::create([
                            'teacher_id' =>
                                $teacher->id,

                            'mosque_id' =>
                                $mosque->id,

                            'name' =>
                                $name,

                            'capacity' =>
                                $capacity,

                            'schedule_days' =>
                                $schedule,

                            'start_time' =>
                                $startTime,

                            'end_time' =>
                                $endTime,

                            'status' =>
                                'active',
                        ]);

                    $halaqat->push(
                        $halaqa
                    );

                    $this->command->info(
                        "  📖 {$name} → المعلم {$teacher->id}"
                    );

                    $teacherIndex++;
                }
            }

            $this->command->info('');

            $this->command->info(
                '📖 إجمالي الحلقات: ' .
                $halaqat->count()
            );

            // =========================================================
            // 10. إنشاء الطلاب
            // =========================================================

            $studentHalaqaMap = [];

            $studentCount = 0;

            $this->command->info('');

            $this->command->info(
                "👦 إنشاء {$this->studentsCount} طالب..."
            );

            // =========================================================
            // حلقات المسجد الأول
            // =========================================================

            $firstMosqueHalaqat =
                $halaqat
                    ->where(
                        'mosque_id',
                        $firstMosque->id
                    )
                    ->values();

            if (
                $firstMosqueHalaqat->count()
                !==
                $this->firstMosqueHalaqat
            ) {

                throw new \Exception(
                    "❌ المسجد الأول يجب أن يحتوي على " .
                    "{$this->firstMosqueHalaqat} حلقات."
                );
            }

            // =========================================================
            // حلقات باقي المساجد
            // =========================================================

            $otherHalaqat =
                $halaqat
                    ->where(
                        'mosque_id',
                        '!=',
                        $firstMosque->id
                    )
                    ->values();

            // =========================================================
            // إنشاء 20 طالبًا في كل حلقة للمسجد الأول
            // =========================================================

            $this->command->info('');

            $this->command->info(
                "⭐ إنشاء {$this->studentsPerFirstMosqueHalaqa} طالب في كل حلقة للمسجد الأول..."
            );

            foreach (
                $firstMosqueHalaqat as $halaqa
            ) {

                $this->command->info(
                    "  📖 {$halaqa->name}"
                );

                for (
                    $j = 0;
                    $j <
                    $this->studentsPerFirstMosqueHalaqa;
                    $j++
                ) {

                    // =================================================
                    // اختيار ولي الأمر
                    // =================================================

                    $parent =
                        $parents[
                        $studentCount
                        %
                        $parents->count()
                        ];

                    // =================================================
                    // إنشاء الطالب
                    // =================================================

                    $student =
                        Student::create([
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
                                        rand(
                                            7,
                                            15
                                        )
                                    )
                                    ->subDays(
                                        rand(
                                            0,
                                            364
                                        )
                                    )
                                    ->toDateString(),

                            'gender' =>
                                'male',

                            'status' =>
                                'active',

                            /*
                             * الطالب مرتبط بحلقة واحدة فقط.
                             */
                            'halaqa_id' =>
                                $halaqa->id,
                        ]);

                    $studentHalaqaMap[
                    $student->id
                    ] =
                        $halaqa->id;

                    $studentCount++;
                }

                $this->command->info(
                    "     ✓ تم إنشاء {$this->studentsPerFirstMosqueHalaqa} طالب"
                );
            }

            // =========================================================
            // الطلاب المتبقون
            // =========================================================

            $remainingStudents =
                $this->studentsCount -
                $studentCount;

            $this->command->info('');

            $this->command->info(
                "👦 الطلاب المتبقون للمساجد الأخرى: {$remainingStudents}"
            );

            if (
                $remainingStudents > 0 &&
                $otherHalaqat->isEmpty()
            ) {

                throw new \Exception(
                    '❌ لا توجد حلقات في المساجد الأخرى لتوزيع الطلاب المتبقين.'
                );
            }

            // =========================================================
            // توزيع الطلاب المتبقين
            // =========================================================

            for (
                $i = 0;
                $i < $remainingStudents;
                $i++
            ) {

                $halaqa =
                    $otherHalaqat[
                    $i %
                    $otherHalaqat->count()
                    ];

                $parent =
                    $parents[
                    $studentCount
                    %
                    $parents->count()
                    ];

                $student =
                    Student::create([
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
                                    rand(
                                        7,
                                        15
                                    )
                                )
                                ->subDays(
                                    rand(
                                        0,
                                        364
                                    )
                                )
                                ->toDateString(),

                        'gender' =>
                            'male',

                        'status' =>
                            'active',

                        'halaqa_id' =>
                            $halaqa->id,
                    ]);

                $studentHalaqaMap[
                $student->id
                ] =
                    $halaqa->id;

                $studentCount++;
            }

            $this->command->info('');

            $this->command->info(
                "👦 تم إنشاء {$studentCount} طالب."
            );

            // =========================================================
            // التحقق النهائي من عدد الطلاب
            // =========================================================

            if (
                $studentCount !==
                $this->studentsCount
            ) {

                throw new \Exception(
                    "❌ عدد الطلاب غير صحيح. " .
                    "المطلوب {$this->studentsCount}، " .
                    "تم إنشاء {$studentCount}."
                );
            }

            // =========================================================
            // 11. التحقق من المسجد الأول
            // =========================================================

            $firstMosqueStudents =
                Student::where(
                    'mosque_id',
                    $firstMosque->id
                )->count();

            $expectedFirstMosqueStudents =
                $this->firstMosqueHalaqat *
                $this->studentsPerFirstMosqueHalaqa;

            if (
                $firstMosqueStudents !==
                $expectedFirstMosqueStudents
            ) {

                throw new \Exception(
                    "❌ عدد طلاب المسجد الأول غير صحيح. " .
                    "المطلوب {$expectedFirstMosqueStudents}، " .
                    "تم إنشاء {$firstMosqueStudents}."
                );
            }

            $this->command->info(
                "✅ المسجد الأول يحتوي على {$firstMosqueStudents} طالب."
            );

            // =========================================================
            // 12. إنشاء أعذار الغياب
            // =========================================================

            $this->command->info(
                '📋 إنشاء أعذار الغياب...'
            );

            $studentsForExcuses =
                Student::query()
                    ->whereIn(
                        'id',
                        array_keys(
                            $studentHalaqaMap
                        )
                    )
                    ->inRandomOrder()
                    ->take(
                        min(
                            20,
                            count(
                                $studentHalaqaMap
                            )
                        )
                    )
                    ->get();

            foreach (
                $studentsForExcuses as $student
            ) {

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
                                rand(
                                    1,
                                    10
                                )
                            )
                            ->toDateString(),

                    'reason' =>
                        collect(
                            $this->excuseReasons
                        )->random(),

                    'status' =>
                        'pending',

                    'admin_comment' =>
                        null,
                ]);
            }

            $this->command->info(
                '✅ تم إنشاء أعذار الغياب.'
            );

            // =========================================================
            // 13. Attendance + Evaluations
            // =========================================================

            $this->command->info('');

            $this->command->info(
                '🔄 جاري إنشاء الحضور والتقييمات...'
            );

            $attendancesData = [];

            $evaluationsData = [];

            $nowStr =
                Carbon::now()
                    ->toDateTimeString();

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
                            ->subDays(
                                $day
                            );

                    $dayName =
                        strtolower(
                            $date->format(
                                'l'
                            )
                        );

                    $scheduleDays =
                        $halaqa->schedule_days
                        ?? [];

                    if (
                        !in_array(
                            $dayName,
                            $scheduleDays
                        )
                    ) {
                        continue;
                    }

                    $status =
                        collect([
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

                        'notes' =>
                            null,

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
                    rand(
                        5,
                        15
                    );

                for (
                    $i = 0;
                    $i < $evalCount;
                    $i++
                ) {

                    $from =
                        rand(
                            1,
                            100
                        );

                    $to =
                        min(
                            $from +
                            rand(
                                2,
                                15
                            ),
                            286
                        );

                    $evaluationsData[] = [
                        'client_uuid' =>
                            Str::uuid()
                                ->toString(),

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
                                    rand(
                                        1,
                                        30
                                    )
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
            // 14. Bulk Attendance
            // =========================================================

            $this->command->info(
                '⚡ إدخال ' .
                count(
                    $attendancesData
                ) .
                ' سجل حضور...'
            );

            foreach (
                array_chunk(
                    $attendancesData,
                    500
                ) as $chunk
            ) {

                Attendance::insert(
                    $chunk
                );
            }

            // =========================================================
            // 15. Bulk Evaluations
            // =========================================================

            $this->command->info(
                '⚡ إدخال ' .
                count(
                    $evaluationsData
                ) .
                ' سجل تقييم...'
            );

            foreach (
                array_chunk(
                    $evaluationsData,
                    500
                ) as $chunk
            ) {

                Evaluation::insert(
                    $chunk
                );
            }

            // =========================================================
            // 16. Commit
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

            // =========================================================
            // Summary
            // =========================================================

            $this->printSummary(
                $studentCount,
                $halaqat->count(),
                $teachers->count(),
                $parents->count(),
                count(
                    $attendancesData
                ),
                count(
                    $evaluationsData
                ),
                $firstMosqueStudents
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
    | Clear Education Data
    |--------------------------------------------------------------------------
    */

    /**
     * حذف بيانات Education فقط.
     *
     * لا يتم حذف:
     * - Users
     * - Roles
     * - Mosques
     * - Teacher Profiles
     */
    private function clearEducationData(): void
    {
        $this->command->warn(
            '🧹 حذف بيانات Education القديمة...'
        );

        /*
         * الحذف من الجداول التابعة إلى الجداول الأساسية.
         */

        AttendanceExcuse::query()->delete();

        Attendance::query()->delete();

        Evaluation::query()->delete();

        Student::query()->delete();

        Halaqa::query()->delete();

        $this->command->info(
            '✅ تم حذف بيانات Education القديمة.'
        );
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
        int $parents,
        int $attendance,
        int $evaluations,
        int $firstMosqueStudents
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
            '👨‍👩‍👧 أولياء الأمور: ' .
            $parents
        );

        $this->command->info(
            '📖 الحلقات: ' .
            $halaqat
        );

        $this->command->info(
            '⭐ حلقات المسجد الأول: ' .
            $this->firstMosqueHalaqat
        );

        $this->command->info(
            '👦 طلاب المسجد الأول: ' .
            $firstMosqueStudents
        );

        $this->command->info(
            '👦 إجمالي الطلاب: ' .
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
