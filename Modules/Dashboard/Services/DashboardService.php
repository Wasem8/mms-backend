<?php

namespace Modules\Dashboard\Services;

use Modules\Education\Models\Student;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation;
use Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // 💡 أضفنا مصفوفة الفلاتر هنا كبارامتر اختياري
    public function getSupervisorStats($mosqueId, array $filters = [])
    {
        return [
            'cards' => $this->getCardsStats($mosqueId, $filters),
            'weekly_attendance' => $this->getWeeklyAttendance($mosqueId, $filters),
            'quran_progress' => $this->getQuranProgressCurve($mosqueId, $filters),
            'top_teachers' => $this->getTopAchievingTeachers($mosqueId), // المعلمون يبقون على مستوى المسجد غالباً
            'absenteeism_report' => $this->getGeneralAbsenteeismReport($mosqueId, $filters),
            'recent_activities' => $this->getRecentActivities($mosqueId, $filters),
        ];
    }

    private function getCardsStats($mosqueId, $filters)
    {
        $studentQuery = Student::where('mosque_id', $mosqueId);
        $halaqaQuery = Halaqa::where('mosque_id', $mosqueId);

        if (!empty($filters['halaqa_id'])) {
            $studentQuery->whereHas('halaqats', fn($q) => $q->where('halaqats.id', $filters['halaqa_id']));
            $halaqaQuery->where('id', $filters['halaqa_id']);
        }

        $totalStudents = $studentQuery->count();

        $today = Carbon::today()->toDateString();
        $attendanceQuery = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
            ->whereDate('date', $today);

        if (!empty($filters['halaqa_id'])) {
            $attendanceQuery->where('halaqa_id', $filters['halaqa_id']);
        }

        $totalAttendanceToday = (clone $attendanceQuery)->count();
        $presentCount = $attendanceQuery->where('status', 'present')->count();

        $rate = $totalAttendanceToday > 0 ? round(($presentCount / $totalAttendanceToday) * 100) : 0;

        return [
            'total_students' => $totalStudents,
            'total_teachers' => User::role('teacher')->where('mosque_id', $mosqueId)->count(),
            'total_halaqas' => $halaqaQuery->count(),
            'attendance_today_percentage' => $rate . '%',
        ];
    }
    private function getWeeklyAttendance($mosqueId, $filters)
    {
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $last7Days->push(Carbon::today()->subDays($i));
        }

        return $last7Days->map(function ($date) use ($mosqueId, $filters) {
            $formattedDate = $date->toDateString();

            $query = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
                ->whereDate('date', $formattedDate);

            // 🆕 تطبيق فلترة الحلقة على الشارت الأسبوعي
            if (!empty($filters['halaqa_id'])) {
                $query->where('halaqa_id', $filters['halaqa_id']);
            }

            $stats = $query->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            return [
                'day' => $date->translatedFormat('l'),
                'percentage' => $stats->total > 0 ? round(($stats->present / $stats->total) * 100) : 0
            ];
        });
    }

    private function getQuranProgressCurve($mosqueId, $filters)
    {
        $months = collect();
        for ($i = 4; $i >= 0; $i--) {
            $months->push(Carbon::today()->subMonths($i));
        }

        return $months->map(function ($month) use ($mosqueId, $filters) {
            $query = Evaluation::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
                ->whereMonth('evaluated_at', $month->month)
                ->whereYear('evaluated_at', $month->year);

            if (!empty($filters['halaqa_id'])) {
                $query->where('halaqa_id', $filters['halaqa_id']);
            }

            $stats = $query->selectRaw("AVG(score) as average_score, COUNT(id) as total_evaluations")
                ->first();

            return [
                'month' => $month->translatedFormat('F'),
                'average_score' => $stats->average_score ? round($stats->average_score, 1) : 0,
                'total_evaluations' => (int)($stats->total_evaluations ?? 0)
            ];
        });
    }

    private function getTopAchievingTeachers($mosqueId)
    {
        return User::role('teacher')
            ->where('users.mosque_id', $mosqueId)
            ->join('halaqats', 'users.id', '=', 'halaqats.teacher_id')
            ->join('evaluations', 'halaqats.id', '=', 'evaluations.halaqa_id')
            ->select('users.id', 'users.name')
            ->selectRaw('COALESCE(SUM(evaluations.to_ayah - evaluations.from_ayah + 1), 0) as total_ayahs_reviewed')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_ayahs_reviewed')
            ->take(5)
            ->get();
    }

    private function getGeneralAbsenteeismReport($mosqueId, $filters)
    {
        $query = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
            ->where('status', 'absent')
            ->whereMonth('date', Carbon::today()->month);


        if (!empty($filters['halaqa_id'])) {
            $query->where('halaqa_id', $filters['halaqa_id']);
        }

        return $query->with(['student:id,first_name,last_name', 'halaqa:id,name'])
            ->select('student_id', 'halaqa_id', DB::raw('COUNT(*) as total_absent_days'))
            ->groupBy('student_id', 'halaqa_id')
            ->orderByDesc('total_absent_days')
            ->take(5)
            ->get()
            ->map(fn($row) => [
                'student_name' => $row->student ? "{$row->student->first_name} {$row->student->last_name}" : 'طالب محذوف',
                'halaqa_name' => $row->halaqa?->name ?? 'بدون حلقة',
                'absent_days_this_month' => $row->total_absent_days
            ]);
    }

    private function getRecentActivities($mosqueId, $filters)
    {
        $studentQuery = Student::where('mosque_id', $mosqueId);
        $attendanceQuery = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))->with(['student', 'halaqa']);

        if (!empty($filters['halaqa_id'])) {
            $attendanceQuery->where('halaqa_id', $filters['halaqa_id']);
            $studentQuery->whereHas('halaqats', fn($q) => $q->where('halaqats.id', $filters['halaqa_id']));
        }

        $recentStudents = $studentQuery->latest()->take(3)->get()->map(fn($s) => [
            'title' => 'انضمام طالب جديد',
            'description' => "{$s->first_name} {$s->last_name}",
            'time' => $s->created_at->diffForHumans(),
            'type' => 'user'
        ]);

        $recentAttendance = $attendanceQuery->latest()->take(2)->get()->map(fn($a) => [
            'title' => "تم تسجيل حضور حلقة {$a->halaqa?->name}",
            'description' => "بواسطة " . ($a->halaqa?->teacher?->name ?? 'المعلم'),
            'time' => $a->created_at->diffForHumans(),
            'type' => 'success'
        ]);

        return $recentAttendance->concat($recentStudents)->sortByDesc('time')->values()->all();
    }

    /**
     * الحصول على إحصائيات المشرف بصيغة PDF
     * تعيد البيانات بالصيغة المتوقعة من قبل view الـ PDF
     */
    public function getSupervisorStatsForPdf($mosqueId, array $filters = [])
    {
        $cardsStats = $this->getCardsStats($mosqueId, $filters);

        return [
            'halaqat_count' => $cardsStats['total_halaqas'],
            'students_count' => $cardsStats['total_students'],
            'teachers_count' => $cardsStats['total_teachers'],
            'attendance_rate' => (int) str_replace('%', '', $cardsStats['attendance_today_percentage']),
            'halaqat' => $this->getHalaqatDetailsForPdf($mosqueId, $filters),
        ];
    }

    /**
     * الحصول على تفاصيل الحلقات للـ PDF
     */
    private function getHalaqatDetailsForPdf($mosqueId, array $filters = [])
    {
        $query = Halaqa::where('mosque_id', $mosqueId)
            ->with('teacher')
            ->withCount('students');

        if (!empty($filters['halaqa_id'])) {
            $query->where('id', $filters['halaqa_id']);
        }

        return $query->get()->map(function ($halaqa) {
            $today = Carbon::today()->toDateString();

            $attendanceQuery = Attendance::where('halaqa_id', $halaqa->id)
                ->whereDate('date', $today);

            $totalAttendance = (clone $attendanceQuery)->count();
            $presentCount = $attendanceQuery->where('status', 'present')->count();

            $attendanceRate = $totalAttendance > 0
                ? round(($presentCount / $totalAttendance) * 100)
                : 0;

            return [
                'name' => $halaqa->name,
                'teacher_name' => $halaqa->teacher?->name ?? '-',
                'students_count' => $halaqa->students_count ?? 0,
                'attendance_rate' => $attendanceRate
            ];
        })->toArray();
    }

}
