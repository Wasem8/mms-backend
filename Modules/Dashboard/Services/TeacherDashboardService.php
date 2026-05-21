<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;

class TeacherDashboardService
{

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
}
