<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
    private array $firstNames = [
        'محمد', 'أحمد', 'علي', 'فاطمة', 'عائشة', 'خديجة',
        'حسن', 'حسين', 'إبراهيم', 'موسى', 'عمر', 'سارة',
        'مريم', 'زينب', 'هند', 'آمنة', 'نور', 'ياسر',
        'سلمى', 'ليلى'
    ];

    private array $lastNames = [
        'محمود', 'علي', 'الشامي', 'الخطيب', 'الحموي',
        'الدمشقي', 'الحلبي', 'العمر', 'الحسن', 'النعسان',
        'الرفاعي', 'القادري', 'القرشي', 'المالكي', 'الحسيني'
    ];

    private array $specializations = [
        'عاصم عن حفص والتجويد المتقدم',
        'قراءات العشر والأساسيات',
        'تجويد وتحفيظ أجزاء عم وتبارك',
        'مراجعة وتثبيت القرآن كامل'
    ];

    private array $surahs = [
        'الفاتحة', 'البقرة', 'آل عمران', 'النساء', 'المائدة',
        'الأنعام', 'الأعراف', 'الأنفال', 'التوبة', 'يونس',
        'هود', 'يوسف', 'الرعد', 'إبراهيم', 'الحجر', 'النحل',
        'الإسراء', 'الكهف'
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
        'متقدم'
    ];

    private array $excuseReasons = [
        'وعكة صحية',
        'ظرف عائلي طارئ',
        'مرض مفاجئ',
        'سفر ضروري',
        'موعد طبي',
        'ظروف قاهرة'
    ];

    private array $schedules = [
        ['saturday', 'monday', 'wednesday'],
        ['sunday', 'tuesday', 'thursday'],
        ['monday', 'wednesday', 'friday'],
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {

            $this->command->info(
                '🔄 بدء إنشاء بيانات التعليم وربطها بالمساجد والمستخدمين الموجودين...'
            );

            // =========================================================
            // 1. جلب المساجد التي أنشأها MosqueSeeder
            // =========================================================

            $mosques = Mosque::query()
                ->orderBy('id')
                ->get();

            if ($mosques->isEmpty()) {
                throw new \Exception(
                    '❌ لا توجد مساجد في قاعدة البيانات. شغّل MosqueSeeder أولاً.'
                );
            }

            $mosqueIds = $mosques->pluck('id')->values()->toArray();

            $this->command->info(
                '🕌 تم العثور على ' . count($mosqueIds) . ' مسجد موجود مسبقاً.'
            );

            // =========================================================
            // 2. جلب Roles
            // =========================================================

            $parentRole = Role::where('name', 'parent')->first();
            $teacherRole = Role::where('name', 'teacher')->first();

            if (!$parentRole) {
                throw new \Exception(
                    '❌ Role "parent" غير موجود. شغّل RoleSeeder أولاً.'
                );
            }

            if (!$teacherRole) {
                throw new \Exception(
                    '❌ Role "teacher" غير موجود. شغّل RoleSeeder أولاً.'
                );
            }

            // =========================================================
            // 3. جلب المعلمين الموجودين من UserSeeder
            // =========================================================

            $teachers = User::whereHas('roles', function ($query) use ($teacherRole) {
                $query->where('roles.id', $teacherRole->id);
            })
                ->orderBy('id')
                ->get();

            if ($teachers->isEmpty()) {
                throw new \Exception(
                    '❌ لا يوجد مستخدمون بدور teacher. شغّل UserSeeder أولاً.'
                );
            }

            // =========================================================
            // 4. جلب أولياء الأمور الموجودين من UserSeeder
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
                '👨‍🏫 المعلمون الموجودون: ' . $teachers->count()
            );

            $this->command->info(
                '👨‍👩‍👧 أولياء الأمور الموجودون: ' . $parents->count()
            );

            // =========================================================
            // 5. إنشاء TeacherProfile للمعلمين
            // =========================================================

            foreach ($teachers as $teacher) {

                TeacherProfile::updateOrCreate(
                    [
                        'user_id' => $teacher->id,
                    ],
                    [
                        'phone' => $teacher->phone
                            ?? '+9639' . rand(10000000, 99999999),

                        'specialization' => collect(
                            $this->specializations
                        )->random(),

                        'status' => 'active',

                        'notes' => 'معلم قرآن كريم وحلقة تحفيظ.',
                    ]
                );
            }

            $this->command->info(
                '✅ تم تجهيز ملفات المعلمين Teacher Profiles.'
            );

            // =========================================================
            // 6. إنشاء حلقات للمعلمين
            // =========================================================

            $halaqat = collect();

            $counter = 1;

            foreach ($teachers as $index => $teacher) {

                /*
                 * إذا كان للمعلم مسجد محدد من UserSeeder
                 * نستخدمه.
                 *
                 * وإلا نربطه بأحد المساجد الموجودة.
                 */

                $mosqueId = $teacher->mosque_id;

                if (!$mosqueId || !in_array($mosqueId, $mosqueIds)) {
                    $mosqueId = $mosqueIds[$index % count($mosqueIds)];

                    $teacher->update([
                        'mosque_id' => $mosqueId,
                    ]);
                }

                $startTimes = [
                    '16:00',
                    '17:30',
                    '18:00',
                    '19:30'
                ];

                $startTime = $startTimes[$index % count($startTimes)];

                $endTime = Carbon::createFromFormat(
                    'H:i',
                    $startTime
                )
                    ->addMinutes(90)
                    ->format('H:i:s');

                $halaqa = Halaqa::firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'mosque_id' => $mosqueId,
                        'name' => 'حلقة ' . $counter . ' - ' . $teacher->name,
                    ],
                    [
                        'capacity' => rand(15, 30),

                        'schedule_days' =>
                            $this->schedules[
                            $index % count($this->schedules)
                            ],

                        'start_time' => $startTime,

                        'end_time' => $endTime,

                        'status' => 'active',
                    ]
                );

                $halaqat->push($halaqa);

                $counter++;
            }

            $this->command->info(
                '📖 تم إنشاء/تجهيز ' . $halaqat->count() . ' حلقة.'
            );

            // =========================================================
            // 7. إنشاء الطلاب
            // =========================================================

            $studentHalaqaMap = [];

            $studentCount = 0;

            /*
             * ننشئ 200 طالب تقريباً.
             */

            for ($i = 1; $i <= 200; $i++) {

                if ($halaqat->isEmpty()) {
                    break;
                }

                $halaqa = $halaqat[$i % $halaqat->count()];

                /*
                 * نختار ولي أمر من المستخدمين الموجودين.
                 */

                $parent = $parents[$i % $parents->count()];

                $student = Student::create([
                    'first_name' => collect(
                        $this->firstNames
                    )->random(),

                    'last_name' => collect(
                        $this->lastNames
                    )->random(),

                    'parent_id' => $parent->id,

                    'mosque_id' => $halaqa->mosque_id,

                    'date_of_birth' => now()
                        ->subYears(rand(7, 15))
                        ->toDateString(),

                    'gender' => collect([
                        'male',
                        'female'
                    ])->random(),

                    'status' => 'active',

                    'halaqa_id' => $halaqa->id,
                ]);

                $studentHalaqaMap[$student->id] = $halaqa->id;

                $studentCount++;

                if ($studentCount % 50 === 0) {
                    $this->command->info(
                        "  ➜ تم إنشاء {$studentCount} طالب..."
                    );
                }
            }

            $this->command->info(
                '👦 تم إنشاء ' . $studentCount . ' طالب.'
            );

            // =========================================================
            // 8. Attendance Excuses
            // =========================================================

            $studentsForExcuses = Student::query()
                ->whereIn('id', array_keys($studentHalaqaMap))
                ->inRandomOrder()
                ->take(10)
                ->get();

            foreach ($studentsForExcuses as $student) {

                AttendanceExcuse::create([
                    'student_id' => $student->id,

                    'halaqa_id' => $student->halaqa_id,

                    'parent_id' => $student->parent_id,

                    'absence_date' => now()
                        ->subDays(rand(1, 10))
                        ->toDateString(),

                    'reason' => collect(
                        $this->excuseReasons
                    )->random(),

                    'status' => 'pending',

                    'admin_comment' => null,
                ]);
            }

            $this->command->info(
                '📋 تم إنشاء أعذار الغياب.'
            );

            // =========================================================
            // 9. Attendance + Evaluations
            // =========================================================

            $this->command->info(
                '🔄 جاري إنشاء الحضور والتقييمات...'
            );

            $attendancesData = [];
            $evaluationsData = [];

            $nowStr = Carbon::now()->toDateTimeString();

            foreach ($studentHalaqaMap as $studentId => $halaqaId) {

                $halaqa = $halaqat->firstWhere(
                    'id',
                    $halaqaId
                );

                if (!$halaqa) {
                    continue;
                }

                // -----------------------------------------------------
                // Attendance
                // -----------------------------------------------------

                for ($day = 30; $day >= 0; $day--) {

                    $date = Carbon::today()->subDays($day);

                    $dayName = strtolower(
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

                    $attendancesData[] = [
                        'halaqa_id' => $halaqaId,

                        'student_id' => $studentId,

                        'date' => $date->toDateString(),

                        'status' => collect([
                            'present',
                            'present',
                            'present',
                            'late',
                            'absent'
                        ])->random(),

                        'notes' => null,

                        'created_at' => $nowStr,

                        'updated_at' => $nowStr,
                    ];
                }

                // -----------------------------------------------------
                // Evaluations
                // -----------------------------------------------------

                $evalCount = rand(5, 15);

                for ($i = 0; $i < $evalCount; $i++) {

                    $from = rand(1, 100);

                    $evaluationsData[] = [
                        'client_uuid' => Str::uuid()->toString(),

                        'halaqa_id' => $halaqaId,

                        'student_id' => $studentId,

                        'surah_name' => collect(
                            $this->surahs
                        )->random(),

                        'from_ayah' => $from,

                        'to_ayah' => min(
                            $from + rand(2, 15),
                            286
                        ),

                        'score' => collect([
                            75,
                            80,
                            85,
                            90,
                            95,
                            100
                        ])->random(),

                        'notes' => collect(
                            $this->evaluationNotes
                        )->random(),

                        'evaluated_at' => now()
                            ->subDays(rand(1, 30))
                            ->toDateString(),

                        'created_at' => $nowStr,

                        'updated_at' => $nowStr,
                    ];
                }
            }

            // =========================================================
            // 10. Bulk Attendance
            // =========================================================

            $this->command->info(
                '⚡ إدخال ' .
                count($attendancesData) .
                ' سجل حضور...'
            );

            foreach (
                array_chunk($attendancesData, 500)
                as $chunk
            ) {
                Attendance::insert($chunk);
            }

            // =========================================================
            // 11. Bulk Evaluations
            // =========================================================

            $this->command->info(
                '⚡ إدخال ' .
                count($evaluationsData) .
                ' سجل تقييم...'
            );

            foreach (
                array_chunk($evaluationsData, 500)
                as $chunk
            ) {
                Evaluation::insert($chunk);
            }

            // =========================================================
            // 12. Commit
            // =========================================================

            DB::commit();

            $this->command->info(
                '✅ تم تنفيذ EducationDatabaseSeeder بنجاح.'
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

            $this->command->error(
                '❌ حدث خطأ: ' . $e->getMessage()
            );

            $this->command->error(
                '📍 السطر: ' . $e->getLine()
            );

            throw $e;
        }
    }

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
            '🕌 المساجد: ' . Mosque::count()
        );

        $this->command->info(
            '👨‍🏫 المعلمون: ' . $teachers
        );

        $this->command->info(
            '📖 الحلقات: ' . $halaqat
        );

        $this->command->info(
            '👦 الطلاب: ' . $students
        );

        $this->command->info(
            '📝 سجلات الحضور: ' . $attendance
        );

        $this->command->info(
            '⭐ التقييمات: ' . $evaluations
        );

        $this->command->info(
            '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
        );
    }
}
