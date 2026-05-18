<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation; // 💡 تم إضافة موديل التقييمات
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EducationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 🕌 1. جلب المساجد والتأكد من وجودها
            $mosques = Mosque::take(2)->get();
            $mosque1 = $mosques[0] ?? null;
            $mosque2 = $mosques[1] ?? null;

            if (!$mosque1) {
                // خطوة احترازية إذا كانت قاعدة البيانات فارغة تماماً من المساجد
                $mosque1 = Mosque::create(['name' => 'المسجد الكبير']);
                $mosque2 = Mosque::create(['name' => 'مسجد النور']);
            }

            // 👨‍🏫 2. إنشاء المدرسين وربطهم بالمسجد الأول
            $teacher = User::firstOrCreate(
                ['email' => 'teacher@test.com'],
                [
                    'name' => 'Teacher One',
                    'password' => bcrypt('password'),
                    'mosque_id' => $mosque1->id, // 💡 ربط المعلم بالمسجد
                ]
            );

            if (!$teacher->hasRole('teacher')) {
                $teacher->assignRole('teacher');
            }

            // 📚 3. إنشاء الحلقات
            $halaqa1 = Halaqa::create([
                'name' => 'حلقة التحفيظ - المستوى الأول',
                'teacher_id' => $teacher->id,
                'mosque_id' => $mosque1->id,
                'capacity' => 10,
                'schedule_days' => ['sunday', 'tuesday', 'thursday'],
                'start_time' => '16:00',
                'end_time' => '18:00',
                'status' => 'active',
            ]);

            $halaqa2 = Halaqa::create([
                'name' => 'حلقة النور',
                'teacher_id' => $teacher->id,
                'mosque_id' => $mosque2->id,
                'capacity' => 15,
                'schedule_days' => ['saturday', 'monday'],
                'start_time' => '15:00',
                'end_time' => '17:00',
                'status' => 'active',
            ]);

            // 👦 4. إنشاء الطلاب
            $students = collect();
            for ($i = 1; $i <= 10; $i++) {
                $students->push(
                    Student::create([
                        'first_name' => "Student",
                        'last_name' => "$i",
                        'mosque_id' => $i <= 5 ? $mosque1->id : $mosque2->id,
                        'date_of_birth' => now()->subYears(10 + $i)->toDateString(),
                        'status' => 'active',
                    ])
                );
            }

            // 🔗 5. ربط الطلاب بالحلقات
            $halaqa1->students()->attach(
                $students->take(5)->pluck('id')->mapWithKeys(fn ($id) => [
                    $id => ['joined_at' => now(), 'status' => 'active']
                ])
            );

            $halaqa2->students()->attach(
                $students->skip(5)->pluck('id')->mapWithKeys(fn ($id) => [
                    $id => ['joined_at' => now(), 'status' => 'active']
                ])
            );

            // 📅 6. توليد سجل حضور وغياب مكثف (لآخر 7 أيام) لتغذية الشارت الأسبوعي
            for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--) {
                $targetDate = Carbon::today()->subDays($dayOffset);

                foreach ($students as $student) {
                    // جعل الطالب رقم 1 يغيب كثيراً ليظهر بوضوح في "تقرير الغياب العام"
                    if ($student->id == 1 && $dayOffset % 2 == 0) {
                        $status = 'absent';
                    } else {
                        $status = collect(['present', 'present', 'present', 'late', 'absent'])->random();
                    }

                    Attendance::create([
                        'halaqa_id' => $student->id <= 5 ? $halaqa1->id : $halaqa2->id,
                        'student_id' => $student->id,
                        'date' => $targetDate->toDateString(),
                        'status' => $status,
                        'notes' => $status === 'absent' ? 'غياب بدون عذر' : null,
                    ]);
                }
            }

            // 📝 7. توليد بيانات تقييمات (Evaluations) لآخر 5 أشهر لتغذية منحنى الحفظ
            $surahs = ['البقرة', 'آل عمران', 'النساء', 'المائدة', 'الأنعام'];

            for ($monthOffset = 4; $monthOffset >= 0; $monthOffset--) {
                $evaluatedMonth = Carbon::today()->subMonths($monthOffset);

                // نختار عينة عشوائية من الطلاب لوضع تقييمات لهم كل شهر
                foreach ($students as $student) {
                    $fromAyah = rand(1, 50);
                    $toAyah = $fromAyah + rand(10, 30); // توليد فارق آيات مناسب للإنجاز

                    Evaluation::create([
                        'halaqa_id' => $student->id <= 5 ? $halaqa1->id : $halaqa2->id,
                        'student_id' => $student->id,
                        'surah_name' => collect($surahs)->random(),
                        'from_ayah' => $fromAyah,
                        'to_ayah' => $toAyah,
                        'score' => rand(80, 100), // درجات ممتازة ليرتفع منحنى الأداء
                        'notes' => 'قراءة ممتازة مع مراعاة أحكام التجويد',
                        'evaluated_at' => $evaluatedMonth->subDays(rand(1, 20))->toDateTimeString(),
                    ]);
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
