<?php

namespace Modules\Education\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\User\Models\User;
use Modules\User\Models\Role;

use Modules\Mosque\Models\Mosque;

use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation;

class EducationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | MOSQUES
            |--------------------------------------------------------------------------
            */

            $mosque1 = Mosque::firstOrCreate([
                'name' => 'مسجد النور'
            ]);

            $mosque2 = Mosque::firstOrCreate([
                'name' => 'المسجد الكبير'
            ]);

            /*
            |--------------------------------------------------------------------------
            | MAIN PARENT (existing)
            |--------------------------------------------------------------------------
            */

            $mainParent = User::where(
                'email',
                'parent@test.com'
            )->first();

            if (!$mainParent) {
                throw new \Exception(
                    'parent@test.com not found'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ROLE
            |--------------------------------------------------------------------------
            */

            $parentRole = Role::where(
                'name',
                'parent'
            )->first();

            /*
            |--------------------------------------------------------------------------
            | SUPERVISORS
            |--------------------------------------------------------------------------
            */

            $supervisors = collect();

            for ($i = 1; $i <= 2; $i++) {

                $supervisor = User::firstOrCreate(
                    ['email' => "supervisor$i@test.com"],
                    [
                        'name' => "Supervisor $i",
                        'password' => bcrypt('password'),
                        'mosque_id' =>
                            $i === 1
                                ? $mosque1->id
                                : $mosque2->id,
                    ]
                );

                $supervisors->push($supervisor);
            }

            /*
|--------------------------------------------------------------------------
| MAIN TEACHER (existing)
|--------------------------------------------------------------------------
*/

            $mainTeacher = User::where(
                'email',
                'teacher@test.com'
            )->first();

            if (!$mainTeacher) {
                throw new \Exception(
                    'teacher@test.com not found'
                );
            }

            /*
  |--------------------------------------------------------------------------
  | TEACHERS
  |--------------------------------------------------------------------------
  */

            $teachers = collect();

            /*
            |--------------------------------------------------------------------------
            | المعلم الرئيسي الموجود مسبقاً
            |--------------------------------------------------------------------------
            */

            $teachers->push($mainTeacher);

            /*
            |--------------------------------------------------------------------------
            | باقي المعلمين
            |--------------------------------------------------------------------------
            */

            for ($i = 1; $i <= 6; $i++) {

                $teacher = User::firstOrCreate(
                    ['email' => "teacher$i@test.com"],
                    [
                        'name' => "Teacher $i",
                        'password' => bcrypt('password'),
                        'mosque_id' =>
                            $i <= 3
                                ? $mosque1->id
                                : $mosque2->id,
                    ]
                );

                $teachers->push($teacher);
            }

            /*
            |--------------------------------------------------------------------------
            | EXTRA PARENTS
            |--------------------------------------------------------------------------
            */

            $parents = collect();

            $parents->push($mainParent);

            for ($i = 1; $i <= 12; $i++) {

                $parent = User::firstOrCreate(
                    ['email' => "parent$i@test.com"],
                    [
                        'name' => "ولي أمر $i",
                        'password' => bcrypt('password'),
                        'mosque_id' =>
                            $i <= 6
                                ? $mosque1->id
                                : $mosque2->id,
                    ]
                );

                if (
                    $parentRole &&
                    !$parent->hasRole('parent')
                ) {
                    $parent->roles()
                        ->syncWithoutDetaching([
                            $parentRole->id
                        ]);
                }

                $parents->push($parent);
            }

            /*
  |--------------------------------------------------------------------------
  | HALAQAT
  |--------------------------------------------------------------------------
  */

            $halaqat = collect();

            $counter = 1;

            foreach ($teachers as $teacher) {

                /*
                |--------------------------------------------------------------------------
                | teacher@test.com => 2 أو 3 حلقات
                |--------------------------------------------------------------------------
                */

                $halaqaCount =
                    $teacher->id === $mainTeacher->id
                        ? rand(2, 3)
                        : 1;

                for ($i = 1; $i <= $halaqaCount; $i++) {

                    $halaqa = Halaqa::create([

                        'name' => 'حلقة ' . $counter,

                        'teacher_id' => $teacher->id,

                        'mosque_id' => $teacher->mosque_id,

                        'capacity' => rand(10, 25),

                        'schedule_days' => collect([
                            ['sunday', 'tuesday', 'thursday'],
                            ['monday', 'wednesday'],
                            ['saturday', 'monday'],
                        ])->random(),

                        'start_time' => '16:00',

                        'end_time' => '18:00',

                        'status' => 'active',
                    ]);

                    $halaqat->push($halaqa);

                    $counter++;
                }
            }

            /*
 |--------------------------------------------------------------------------
 | STUDENTS
 |--------------------------------------------------------------------------
 */

            $students = collect();

            /*
            |--------------------------------------------------------------------------
            | اختار عدد ثابت بين 3 و4 للحساب الرئيسي
            |--------------------------------------------------------------------------
            */

            $mainParentChildrenCount = rand(3, 4);

            for ($i = 1; $i <= 60; $i++) {

                $student = Student::create([

                    'first_name' => 'طالب',

                    'last_name' => $i,

                    /*
                    |--------------------------------------------------------------------------
                    | فقط أول 3 أو 4 للحساب الرئيسي
                    |--------------------------------------------------------------------------
                    */
                    'parent_id' =>
                        $i <= $mainParentChildrenCount
                            ? $mainParent->id
                            : $parents
                            ->where('id', '!=', $mainParent->id)
                            ->random()
                            ->id,

                    'mosque_id' =>
                        $i <= 30
                            ? $mosque1->id
                            : $mosque2->id,

                    'date_of_birth' => now()
                        ->subYears(rand(8, 18))
                        ->toDateString(),

                    'status' => 'active',
                ]);

                $students->push($student);
            }

            /*
            |--------------------------------------------------------------------------
            | ATTACH TO HALAQAT
            |--------------------------------------------------------------------------
            */

            foreach ($students as $student) {

                $halaqa = $halaqat->random();

                $halaqa->students()->attach([

                    $student->id => [

                        'joined_at' => now(),

                        'status' => 'active'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | ATTENDANCE
            |--------------------------------------------------------------------------
            */

            for ($day = 90; $day >= 0; $day--) {

                $date = Carbon::today()
                    ->subDays($day);

                foreach ($students as $student) {

                    $halaqaId = $student
                        ->halaqats()
                        ->first()?->id;

                    if (!$halaqaId) {
                        continue;
                    }

                    $status = collect([
                        'present',
                        'present',
                        'late',
                        'absent',
                        'excused'
                    ])->random();

                    Attendance::create([

                        'halaqa_id' => $halaqaId,

                        'student_id' => $student->id,

                        'date' => $date,

                        'status' => $status,

                        'notes' => null,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | EVALUATIONS
            |--------------------------------------------------------------------------
            */

            $surahs = [

                'الفاتحة',
                'البقرة',
                'آل عمران',
                'النساء',
                'المائدة',
                'الأنعام',
            ];

            for ($month = 11; $month >= 0; $month--) {

                foreach ($students as $student) {

                    $halaqaId = $student
                        ->halaqats()
                        ->first()?->id;

                    if (!$halaqaId) {
                        continue;
                    }

                    for ($i = 0; $i < rand(8, 15); $i++) {

                        $from = rand(1, 150);

                        Evaluation::create([

                            'halaqa_id' => $halaqaId,

                            'student_id' => $student->id,

                            'surah_name' =>
                                collect($surahs)->random(),

                            'from_ayah' => $from,

                            'to_ayah' =>
                                $from + rand(3, 20),

                            'score' =>
                                rand(65, 100),

                            'notes' =>
                                'تقييم دوري',

                            'evaluated_at' =>
                                Carbon::today()
                                    ->subMonths($month)
                                    ->subDays(rand(1, 25)),
                        ]);
                    }
                }
            }

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
