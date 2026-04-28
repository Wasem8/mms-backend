<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\Education\Models\Attendance;
use Modules\User\Models\User;
use Illuminate\Support\Facades\DB;

class EducationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            // 👨‍🏫 1. إنشاء مدرس
            $teacher = User::firstOrCreate(
                ['email' => 'teacher@test.com'],
                [
                    'name' => 'Teacher One',
                    'password' => bcrypt('password'),
                ]
            );

            // (اختياري) تأكد أنه Teacher
            if (! $teacher->hasRole('teacher')) {
                $teacher->assignRole('teacher');
            }

            // 📚 2. إنشاء حلقات


            $halaqa1 = Halaqa::create([
                'name' => 'حلقة التحفيظ - المستوى الأول',
                'teacher_id' => $teacher->id,
                'capacity' => 10,
                'schedule_days' => ['sunday', 'tuesday', 'thursday'],
                'start_time' => '16:00',
                'end_time' => '18:00',
                'level' => 'Beginner',
                'status' => 'active',
            ]);

            $halaqa2 = Halaqa::create([
                'name' => 'حلقة النور',
                'schedule_days' => ['saturday', 'monday'],
                'start_time' => '15:00',
                'end_time' => '17:00',
                'teacher_id' => $teacher->id,
                'capacity' => 15,
                'level' => 'Intermediate',
                'status' => 'active',
            ]);

            // 👦 3. إنشاء طلاب
            $students = collect();

            for ($i = 1; $i <= 10; $i++) {
                $students->push(
                    Student::create([
                        'first_name' => "Student",
                        'last_name' => "$i",
                        'email' => "student$i@test.com",
                        'status' => 'active',
                    ])
                );
            }

            // 🔗 4. ربط الطلاب بالحلقات
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

            // 📅 5. تسجيل الحضور
            foreach ($students as $student) {

                Attendance::create([
                    'halaqa_id' => $student->id <= 5 ? $halaqa1->id : $halaqa2->id,
                    'student_id' => $student->id,
                    'date' => now()->toDateString(),
                    'status' => collect(['present', 'absent', 'late'])->random(),
                    'notes' => null,
                ]);
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
