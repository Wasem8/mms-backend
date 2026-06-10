<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\AttendanceExcuse;
use Modules\Education\Models\Evaluation;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Spatie\Browsershot\Browsershot;

class TeacherDashboardService
{

    public function getTeacherBootstrap($teacherId)
    {
        $halaqat = Halaqa::where('teacher_id', $teacherId)
            ->select('id', 'name')
            ->get();

        $rosters = [];

        foreach ($halaqat as $halaqa) {
            $rosters[$halaqa->id] = $halaqa->students()
                ->select(
                    'students.id',
                    'students.first_name',
                    'students.last_name'
                )
                ->get()
                ->map(fn ($student) => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                ]);
        }

        $pendingExcuses = AttendanceExcuse::with([
            'student:id,first_name,last_name',
            'parent:id,name',
            'halaqa:id,name',
        ])
            ->whereHas('halaqa', fn($q) => $q->where('teacher_id', $teacherId))
            ->where('status', 'pending')
            ->get()
            ->map(function ($excuse) {
                return [
                    'id' => $excuse->id,
                    'student_name' => $excuse->student
                        ? $excuse->student->first_name . ' ' . $excuse->student->last_name
                        : null,
                    'parent_name' => $excuse->parent?->name,
                    'halaqa_name' => $excuse->halaqa?->name,
                    'absence_date' => $excuse->absence_date,
                    'reason' => $excuse->reason,
                    'status' => $excuse->status,
                ];
            });

        return [
            'halaqat' => $halaqat,
            'rosters' => $rosters,
            'pending_excuses' => $pendingExcuses,
            'dashboard' => $this->getTeacherStats($teacherId),
        ];
    }

    /**
     * الحصول على إحصائيات داشبورد المعلم الخاص بحلقة معينة
     */
    public function getTeacherStats($teacherId)
    {
        // 1. جلب الحلقة الخاصة بهذا المعلم أولاً
        $halaqa = Halaqa::where('teacher_id', $teacherId)->first();

        if (!$halaqa) {
            return [
                'has_halaqa' => false,
                'message' => 'لم يتم تعيينك في أي حلقة بعد.'
            ];
        }

        $today = Carbon::today()->toDateString();
        $currentMonth = Carbon::today()->month;

        // 2. إحصائيات الطلاب والحضور
        $totalStudents = Student::whereHas('halaqats', fn($q) => $q->where('halaqats.id', $halaqa->id))->count();

        $todayAttendance = Attendance::where('halaqa_id', $halaqa->id)
            ->whereDate('date', $today)
            ->get();

        $presentCount = $todayAttendance->where('status', 'present')->count();
        $attendanceRate = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;

        // 3. كم طالباً تم تقييمه وتسميعه اليوم؟
        $evaluatedTodayCount = Evaluation::where('halaqa_id', $halaqa->id)
            ->whereDate('evaluated_at', $today)
            ->distinct('student_id')
            ->count();

        // 4. إجمالي صفحات الإنجاز للحلقة هذا الشهر (حساب تقريبي بناءً على عدد الآيات المسجلة)
        $totalAyahsThisMonth = Evaluation::where('halaqa_id', $halaqa->id)
            ->whereMonth('evaluated_at', $currentMonth)
            ->selectRaw('SUM(to_ayah - from_ayah + 1) as total_ayahs')
            ->value('total_ayahs') ?? 0;

        // 5. قائمة الطلاب الذين تغيبوا كثيراً هذا الشهر
        $frequentAbsentees = Attendance::where('halaqa_id', $halaqa->id)
            ->where('status', 'absent')
            ->whereMonth('date', $currentMonth)
            ->with('student:id,first_name,last_name')
            ->select('student_id', DB::raw('COUNT(*) as absent_days'))
            ->groupBy('student_id')
            // ⬇️ التعديل هنا: استبدال الكنية بالدالة الحسابية الحقيقية ليتوافق مع PostgreSQL
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('absent_days')
            ->get()
            ->map(fn($row) => [
                'student_name' => $row->student ? "{$row->student->first_name} {$row->student->last_name}" : 'طالب',
                'absent_days'  => $row->absent_days
            ]);

        return [
            'has_halaqa'   => true,
            'halaqa_name'  => $halaqa->name,
            'cards' => [
                'total_students'       => $totalStudents,
                'evaluated_today'      => "{$evaluatedTodayCount} / {$totalStudents}",
                'attendance_percentage'=> $attendanceRate . '%',
                'month_ayahs_progress' => $totalAyahsThisMonth . ' آية تم تسميعها',
            ],
            'alerts' => [
                'frequent_absentees' => $frequentAbsentees // طلاب يحتاجون تواصل مع أولياء أمورهم
            ],
            'recent_evaluations' => $this->getHalaqaRecentEvaluations($halaqa->id)
        ];
    }

    /**
     * جلب آخر 5 تسميعات تمت في الحلقة لعرضها في الداشبورد
     */
    private function getHalaqaRecentEvaluations($halaqaId)
    {
        return Evaluation::where('halaqa_id', $halaqaId)
            ->with('student:id,first_name,last_name')
            // للتأكد من عدم جلب أي تقييم تم إدخاله بالخطأ بتاريخ مستقبلي
            ->whereDate('evaluated_at', '<=', Carbon::now())
            ->latest('evaluated_at')
            ->take(5)
            ->get()
            ->map(function($ev) {

                return [
                    'student_name' => $ev->student ? "{$ev->student->first_name} {$ev->student->last_name}" : 'طالب',
                    'surah'        => $ev->surah_name ?? 'لم تحدد',
                    'from_ayah'    => $ev->from_ayah,
                    'to_ayah'    => $ev->to_ayah,
                    'score'        => (int) $ev->score,
                    // استخدام parse بأمان مع Carbon
                    'time'         => Carbon::parse($ev->evaluated_at)->diffForHumans()
                ];
            });
    }


    public function generateTeacherReportPdf(int $teacherId): string
    {
        // 1. جلب البيانات من الميثود الخاصة بها داخل نفس السيرفس
        $data = $this->getData($teacherId);

        // 2. تحويل ملف الـ Blade إلى كود HTML نظيف وقابل للقراءة
        $html = view('dashboard::reports.teacher', $data)->render();

        // 3. جلب مسارات الخطوط الافتراضية الخاصة بـ mPDF لدمجها لضمان الاستقرار
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        // 4. إضافة مجلد الخطوط الخاص بمشروعك (الذي يحتوي على خط أميري)
        $fontDirs[] = storage_path('fonts');

        // 5. بناء وإعداد كائن الـ mPDF
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 8,
            'fontDir'       => $fontDirs, // المسارات المدمجة
            'fontdata' => array_merge($fontData, [
                'cairo' => [
                    'R'      => 'Cairo-Regular.ttf',
                    'B'      => 'Cairo-Bold.ttf',
                    'useOTL' => 0xFF, // تشبيك الحروف العربية تلقائياً
                ]
            ]),
            'default_font' => 'cairo'
        ]);

        // 6. كتابة محتوى الـ HTML داخل ملف الـ PDF
        $mpdf->WriteHTML($html);

        // 7. تصدير الملف كـ Binary String ليمر عبر الـ Stream في الـ Controller بأمان
        return $mpdf->Output('', 'S');
    }
    private function getData($teacherId)
    {
        $teacher = auth()->user();

        $halaqa = Halaqa::where(
            'teacher_id',
            $teacherId
        )->first();

        if (!$halaqa) {
            return [
                'has_halaqa' => false
            ];
        }

        $today = Carbon::today();

        /*
        |-----------------------------------
        | Students in halaqa
        |-----------------------------------
        */

        $halaqaStudents = Student::whereHas(
            'halaqats',
            function ($q) use ($halaqa) {
                $q->where(
                    'halaqats.id',
                    $halaqa->id
                );
            }
        )->get();

        /*
        |-----------------------------------
        | Student details
        |-----------------------------------
        */

        $students = $halaqaStudents->map(
            function ($student) use (
                $today,
                $halaqa
            ) {

                $attendanceToday =
                    Attendance::where(
                        'student_id',
                        $student->id
                    )
                        ->where(
                            'halaqa_id',
                            $halaqa->id
                        )
                        ->whereDate(
                            'date',
                            $today
                        )
                        ->value('status')
                    ?? 'لم يرصد';

                $attendancePercentage =
                    Attendance::where(
                        'student_id',
                        $student->id
                    )
                        ->where(
                            'halaqa_id',
                            $halaqa->id
                        )
                        ->whereMonth(
                            'date',
                            now()->month
                        )
                        ->selectRaw("
                        ROUND(
                            (
                                SUM(
                                    CASE
                                        WHEN status='present'
                                        THEN 1
                                        ELSE 0
                                    END
                                )::decimal
                                /
                                NULLIF(COUNT(*),0)
                            ) * 100
                        ) as percentage
                    ")
                        ->value('percentage')
                    ?? 0;

                $averageScore =
                    round(
                        Evaluation::where(
                            'student_id',
                            $student->id
                        )
                            ->where(
                                'halaqa_id',
                                $halaqa->id
                            )
                            ->avg('score')
                        ?? 0
                    );

                $performance = match (true) {
                    $averageScore >= 90 => 'ممتاز',
                    $averageScore >= 75 => 'جيد جداً',
                    $averageScore >= 60 => 'جيد',
                    default => 'بحاجة متابعة',
                };

                return [

                    'name' =>
                        "{$student->first_name} {$student->last_name}",

                    'attendance' =>
                        $attendanceToday,

                    'attendance_percentage' =>
                        $attendancePercentage,

                    'average_score' =>
                        $averageScore,

                    'performance' =>
                        $performance,
                ];
            }
        );

        /*
        |-----------------------------------
        | Attendance summary
        |-----------------------------------
        */

        $attendanceToday =
            Attendance::where(
                'halaqa_id',
                $halaqa->id
            )
                ->whereDate(
                    'date',
                    $today
                )
                ->get();

        $present =
            $attendanceToday
                ->where(
                    'status',
                    'present'
                )
                ->count();

        $studentsCount =
            $halaqaStudents->count();

        $attendanceRate =
            $studentsCount
                ? round(
                ($present / $studentsCount) * 100
            )
                : 0;

        /*
        |-----------------------------------
        | Latest evaluations
        |-----------------------------------
        */

        $evaluations =
            Evaluation::where(
                'halaqa_id',
                $halaqa->id
            )
                ->with('student')
                ->latest('evaluated_at')
                ->take(10)
                ->get();

        /*
        |-----------------------------------
        | General average score
        |-----------------------------------
        */

        $averageScore =
            round(
                $students->avg(
                    'average_score'
                ) ?? 0
            );

        return [

            'has_halaqa' => true,

            'teacher' =>
                $teacher,

            'halaqa' =>
                $halaqa,

            'cards' => [

                'students' =>
                    $studentsCount,

                'attendance_rate' =>
                    $attendanceRate,

                'evaluations_today' =>
                    $evaluations->count(),

                'average_score' =>
                    $averageScore,
            ],

            'students' =>
                $students,

            'evaluations' =>
                $evaluations,
        ];
    }
}
