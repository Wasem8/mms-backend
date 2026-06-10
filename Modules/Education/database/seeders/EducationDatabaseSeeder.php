<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
        'مريم', 'زينب', 'هند', 'آمنة', 'نور', 'ياسر', 'سلمى', 'ليلى'
    ];

    private array $lastNames = [
        'محمود', 'علي', 'الشاملي', 'الغافري', 'النعماني',
        'المالكي', 'الخروصي', 'البوسعيدي', 'الحارثي', 'السليمي',
        'الحسني', 'الدرعي', 'الكندي', 'الصقري', 'البطاشي'
    ];

    private array $surahs = [
        'الفاتحة', 'البقرة', 'آل عمران', 'النساء', 'المائدة', 'الأنعام',
        'الأعراف', 'الأنفال', 'التوبة', 'يونس', 'هود', 'يوسف',
        'الرعد', 'إبراهيم', 'الحجر', 'النحل', 'الإسراء', 'الكهف'
    ];

    private array $evaluationNotes = [
        'ممتاز جداً', 'متميز', 'جيد جداً', 'جيد', 'مقبول',
        'يحتاج تحسين', 'بحاجة لمتابعة', 'متوسط الأداء', 'متقدم'
    ];

    private array $excuseReasons = [
        'وعكة صحية', 'ظرف عائلي طاريء', 'مرض مفاجئ', 'سفر ضروري',
        'موعد طبي', 'ظروف قاهرة'
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
            $this->command->info('🔄🔄🔄 بدء ملء بيانات Education الشاملة والمحصنة... 🔄🔄🔄');

            // ============ 1. MOSQUES ============
            $mosqueIds = [];
            foreach (['مسجد النور', 'المسجد الكبير', 'مسجد الرحمة', 'مسجد التوحيد'] as $name) {
                $m = Mosque::firstOrCreate(['name' => $name]);
                $mosqueIds[] = $m->id;
            }
            $this->command->info('✅ تم تجهيز المساجد');

            // ============ 2. MAIN ACCOUNTS ============
            $mainParent = User::firstOrCreate(
                ['email' => 'parent@test.com'],
                ['name' => 'ولي أمر رئيسي', 'password' => bcrypt('password'), 'mosque_id' => $mosqueIds[0]]
            );

            $mainTeacher = User::firstOrCreate(
                ['email' => 'teacher@test.com'],
                ['name' => 'معلم رئيسي', 'password' => bcrypt('password'), 'mosque_id' => $mosqueIds[0]]
            );

            $parentRole = Role::where('name', 'parent')->first();

            // ============ 3. TEACHERS & PARENTS ============
            $teachers = [$mainTeacher];
            for ($i = 1; $i <= 10; $i++) {
                $teachers[] = User::firstOrCreate(
                    ['email' => "teacher$i@test.com"],
                    ['name' => $this->generateArabicName(), 'password' => bcrypt('password'), 'mosque_id' => $mosqueIds[$i % 4]]
                );
            }

            $parents = [$mainParent];
            for ($i = 1; $i <= 20; $i++) {
                $p = User::firstOrCreate(
                    ['email' => "parent$i@test.com"],
                    ['name' => $this->generateArabicName(), 'password' => bcrypt('password'), 'mosque_id' => $mosqueIds[$i % 4]]
                );
                if ($parentRole && !$p->hasRole('parent')) {
                    $p->roles()->syncWithoutDetaching([$parentRole->id]);
                }
                $parents[] = $p;
            }
            $this->command->info('✅ تم إنشاء المعلمين وأولياء الأمور.');

            // ============ 4. HALAQAT CREATION ============
            $mainHalaqa = Halaqa::create([
                'name' => 'حلقة التميز - ' . $mainTeacher->name,
                'teacher_id' => $mainTeacher->id,
                'mosque_id' => $mainTeacher->mosque_id,
                'capacity' => 25,
                'schedule_days' => ['saturday', 'monday', 'wednesday'],
                'start_time' => '16:00',
                'end_time' => '17:30',
                'status' => 'active',
            ]);

            $counter = 1;
            foreach ($teachers as $teacher) {
                if ($teacher->email === 'teacher@test.com') {
                    continue;
                }

                for ($i = 1; $i <= 2; $i++) {
                    $startTimes = ['16:00', '18:00', '19:30'];
                    $startTime = collect($startTimes)->random();

                    Halaqa::create([
                        'name' => 'حلقة ' . $counter . ' - ' . $teacher->name,
                        'teacher_id' => $teacher->id,
                        'mosque_id' => $teacher->mosque_id,
                        'capacity' => rand(15, 35),
                        'schedule_days' => collect($this->schedules)->random(),
                        'start_time' => $startTime,
                        'end_time' => Carbon::createFromFormat('H:i', $startTime)->addMinutes(90)->format('H:i:s'),
                        'status' => 'active',
                    ]);
                    $counter++;
                }
            }
            $halaqat = Halaqa::all();
            $this->command->info('✅ تم إنشاء وعزل الحلقات. العدد الحالي: ' . $halaqat->count());

            // ============ 5. STUDENTS CREATION ============
            $studentHalaqaMap = [];
            $studentCount = 0;

            for ($k = 1; $k <= 15; $k++) {
                $student = Student::create([
                    'first_name' => collect($this->firstNames)->random(),
                    'last_name' => collect($this->lastNames)->random(),
                    'parent_id' => collect($parents)->random()->id,
                    'mosque_id' => $mainHalaqa->mosque_id,
                    'date_of_birth' => now()->subYears(rand(7, 15))->toDateString(),
                    'gender' => collect(['male', 'female'])->random(),
                    'status' => 'active',
                ]);

                if ($student && $student->id) {
                    $studentHalaqaMap[$student->id] = $mainHalaqa->id;
                    $mainHalaqa->students()->attach([
                        $student->id => [
                            'joined_at' => now()->subDays(rand(1, 30)),
                            'status' => 'active'
                        ]
                    ]);
                    $studentCount++;
                }
            }
            $this->command->info('🎯 تم ربط 15 طالباً بحلقة المعلم الرئيسي بنجاح.');

            $mainStudents = Student::whereHas('halaqats', function ($q) use ($mainHalaqa) {
                $q->where('halaqats.id', $mainHalaqa->id);
            })->take(3)->get();

            foreach ($mainStudents as $student) {
                AttendanceExcuse::create([
                    'student_id' => $student->id,
                    'halaqa_id' => $mainHalaqa->id,
                    'parent_id' => $student->parent_id,
                    'absence_date' => now()->subDays(rand(1, 3))->toDateString(),
                    'reason' => collect($this->excuseReasons)->random(),
                    'status' => 'pending',
                    'admin_comment' => null,
                ]);
            }

            for ($i = 1; $i <= 185; $i++) {
                $assignedMosqueId = $mosqueIds[$i % 4];
                $appropriateHalaqat = $halaqat->where('mosque_id', $assignedMosqueId)->where('id', '!=', $mainHalaqa->id);

                if ($appropriateHalaqat->isEmpty()) {
                    continue;
                }
                $selectedHalaqa = $appropriateHalaqat->random();
                $parent = collect($parents)->random();

                $student = Student::create([
                    'first_name' => collect($this->firstNames)->random(),
                    'last_name' => collect($this->lastNames)->random(),
                    'parent_id' => $parent->id,
                    'mosque_id' => $assignedMosqueId,
                    'date_of_birth' => now()->subYears(rand(7, 15))->toDateString(),
                    'gender' => collect(['male', 'female'])->random(),
                    'status' => 'active',
                ]);

                if ($student && $student->id) {
                    $studentHalaqaMap[$student->id] = $selectedHalaqa->id;
                    $selectedHalaqa->students()->attach([
                        $student->id => [
                            'joined_at' => now()->subDays(rand(1, 30)),
                            'status' => 'active'
                        ]
                    ]);
                    $studentCount++;
                }

                if ($studentCount % 50 === 0) {
                    $this->command->info("  ➜ تم إنشاء $studentCount طالب...");
                }
            }

            $totalStudentsInDB = Student::count();
            $this->command->info('🎯 تم التحقق الفعلي: تم حفظ (' . $totalStudentsInDB . ') طالب في قاعدة البيانات!');

            if ($totalStudentsInDB === 0) {
                throw new \Exception('❌ لا يزال جدول الطلاب فارغاً!');
            }

            // ============ 6. OPTIMIZED ATTENDANCE & EVALUATIONS (BULK INSERT) ============
            $this->command->info('🔄 جاري حقن سجلات الحضور والتقييمات بسرعة فائقة (Bulk)...');

            $attendancesData = [];
            $evaluationsData = [];
            $nowStr = Carbon::now()->toDateTimeString();

            foreach ($studentHalaqaMap as $studentId => $halaqaId) {
                $halaqa = $halaqat->find($halaqaId);
                if (!$halaqa) continue;

                // 1. تجميع بيانات الحضور
                for ($day = 30; $day >= 0; $day--) {
                    $date = Carbon::today()->subDays($day);
                    $dayName = strtolower($date->format('l'));

                    if (!in_array($dayName, $halaqa->schedule_days ?? [])) {
                        continue;
                    }

                    $attendancesData[] = [
                        'halaqa_id'  => $halaqaId,
                        'student_id' => $studentId,
                        'date'       => $date->toDateString(),
                        'status'     => collect(['present', 'present', 'present', 'late', 'absent'])->random(),
                        'notes'      => null,
                        'created_at' => $nowStr,
                        'updated_at' => $nowStr
                    ];
                }

                // 2. تجميع بيانات التقييمات
                $evalCountForStudent = rand(5, 15);
                for ($i = 0; $i < $evalCountForStudent; $i++) {
                    $from = rand(1, 100);
                    $evaluationsData[] = [
                        'client_uuid'  => Str::uuid()->toString(),
                        'halaqa_id'    => $halaqaId,
                        'student_id'   => $studentId,
                        'surah_name'   => collect($this->surahs)->random(),
                        'from_ayah'    => $from,
                        'to_ayah'      => $from + rand(2, 15),
                        'score'        => collect([75, 80, 85, 90, 95, 100])->random(),
                        'notes'        => collect($this->evaluationNotes)->random(),
                        'evaluated_at' => now()->subDays(rand(1, 30))->toDateString(),
                        'created_at'   => $nowStr,
                        'updated_at'   => $nowStr
                    ];
                }
            }

            // 🎯 حقن البيانات على دفعات (Chunks) لضمان أداء خارق دون استهلاك ذاكرة السيرفر
            $this->command->info('⚡ جاري تنفيذ الإدخال الجماعي لـ ' . count($attendancesData) . ' سجل حضور...');
            foreach (array_chunk($attendancesData, 500) as $chunk) {
                Attendance::insert($chunk);
            }

            $this->command->info('⚡ جاري تنفيذ الإدخال الجماعي لـ ' . count($evaluationsData) . ' سجل تقييم...');
            foreach (array_chunk($evaluationsData, 500) as $chunk) {
                Evaluation::insert($chunk);
            }

            DB::commit();
            $this->command->info('✅✅✅ اكتمل السيردر بنجاح وتم حفظ كافة البيانات! ✅✅✅');
            $this->printSummary($totalStudentsInDB, $halaqat->count(), count($attendancesData), count($evaluationsData));

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('❌ حدث خطأ غير متوقع: ' . $e->getMessage());
            $this->command->error('في السطر: ' . $e->getLine());
            throw $e;
        }
    }

    private function generateArabicName(): string
    {
        return collect($this->firstNames)->random() . ' ' . collect($this->lastNames)->random();
    }

    private function printSummary($students, $halaqat, $attendance, $evaluations): void
    {
        $this->command->info('');
        $this->command->info('📊 ملخص البيانات المنشأة:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('👦 الطلاب: ' . $students);
        $this->command->info('📖 الحلقات: ' . $halaqat);
        $this->command->info('📝 سجلات الحضور: ' . $attendance);
        $this->command->info('⭐ التقييمات: ' . $evaluations);
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
