<?php

namespace Modules\Dashboard\Services;

use Modules\Education\Models\Student;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Attendance;
use Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSupervisorStats($mosqueId)
    {
        return [
            'cards' => $this->getCardsStats($mosqueId),
            'weekly_attendance' => $this->getWeeklyAttendance($mosqueId),
            'recent_activities' => $this->getRecentActivities($mosqueId),
        ];
    }

    private function getCardsStats($mosqueId)
    {
        // إحصائيات البطاقات
        $totalStudents = Student::where('mosque_id', $mosqueId)->count();

        // نسبة حضور اليوم
        $today = Carbon::today()->toDateString();
        $totalAttendanceToday = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
            ->whereDate('date', $today)
            ->count();

        $presentCount = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        $rate = $totalAttendanceToday > 0 ? round(($presentCount / $totalAttendanceToday) * 100) : 0;

        return [
            'total_students' => $totalStudents,
            'total_teachers' => User::role('teacher')->where('mosque_id', $mosqueId)->count(),
            'total_halaqas' => Halaqa::where('mosque_id', $mosqueId)->count(),
            'attendance_today_percentage' => $rate . '%',
        ];
    }

    private function getWeeklyAttendance($mosqueId)
    {
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $last7Days->push(Carbon::today()->subDays($i));
        }

        return $last7Days->map(function ($date) use ($mosqueId) {
            $formattedDate = $date->toDateString();

            $stats = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
                ->whereDate('date', $formattedDate)
                ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            return [
                'day' => $date->translatedFormat('l'), // يرجع اسم اليوم بالعربي (أحد، اثنين...)
                'percentage' => $stats->total > 0 ? round(($stats->present / $stats->total) * 100) : 0
            ];
        });
    }

    private function getRecentActivities($mosqueId)
    {
        // جلب آخر الطلاب المنضمين كنشاط حقيقي
        $recentStudents = Student::where('mosque_id', $mosqueId)
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($s) => [
                'title' => 'انضمام طالب جديد',
                'description' => "{$s->first_name} {$s->last_name}",
                'time' => $s->created_at->diffForHumans(),
                'type' => 'user'
            ]);

        // جلب آخر عمليات التحضير
        $recentAttendance = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
            ->with(['student', 'halaqa'])
            ->latest()
            ->take(2)
            ->get()
            ->map(fn($a) => [
                'title' => "تم تسجيل حضور حلقة {$a->halaqa?->name}",
                'description' => "بواسطة " . ($a->halaqa?->teacher?->name ?? 'المعلم'),
                'time' => $a->created_at->diffForHumans(),
                'type' => 'success'
            ]);

        return $recentAttendance->concat($recentStudents)->sortByDesc('time')->values()->all();
    }
}
