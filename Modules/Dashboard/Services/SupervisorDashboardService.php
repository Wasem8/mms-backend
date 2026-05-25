<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Modules\Education\Models\{Halaqa, Student, Attendance, Evaluation};
use Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class SupervisorDashboardService
{
    /*
    |-------------------------------------------------------
    | DASHBOARD (كما هو + لا تغييرات منطقية عليه)
    |-------------------------------------------------------
    */
    public function getDashboard(int $mosqueId)
    {
        return [
            'kpis' => $this->getKpis($mosqueId),
            'halaqat' => $this->getHalaqat($mosqueId),
            'top_teachers' => $this->getTopTeachers($mosqueId),
            'weak_students' => $this->getWeakStudents($mosqueId),
            'alerts' => $this->getAlerts($mosqueId),
        ];
    }

    /*
    |-------------------------------------------------------
    | KPI
    |-------------------------------------------------------
    */
    private function getKpis($mosqueId)
    {
        $students = Student::where('mosque_id', $mosqueId)->count();

        $teachers = User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->count();

        $halaqat = Halaqa::where('mosque_id', $mosqueId)->count();

        $attendance = Attendance::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->whereDate('date', Carbon::today())
            ->get();

        $attendanceRate = $attendance->count()
            ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100)
            : 0;

        $avgScore = Evaluation::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->avg('score');

        return [
            'students' => $students,
            'teachers' => $teachers,
            'halaqat' => $halaqat,
            'attendance_rate' => $attendanceRate,
            'average_score' => round($avgScore ?? 0),
        ];
    }

    /*
    |-------------------------------------------------------
    | HALAQAT
    |-------------------------------------------------------
    */
    private function getHalaqat($mosqueId)
    {
        return Halaqa::with(['teacher', 'students'])
            ->where('mosque_id', $mosqueId)
            ->get()
            ->map(function ($h) {

                $attendance = Attendance::where('halaqa_id', $h->id)
                    ->whereDate('date', Carbon::today())
                    ->get();

                $present = $attendance->where('status', 'present')->count();

                $rate = $attendance->count()
                    ? round(($present / $attendance->count()) * 100)
                    : 0;

                $avg = Evaluation::where('halaqa_id', $h->id)->avg('score');

                return [
                    'name' => $h->name,
                    'teacher' => $h->teacher?->name ?? '-',
                    'students_count' => $h->students->count(),
                    'attendance_rate' => $rate,
                    'average_score' => round($avg ?? 0),
                ];
            });
    }

    /*
    |-------------------------------------------------------
    | TOP TEACHERS
    |-------------------------------------------------------
    */
    private function getTopTeachers($mosqueId)
    {
        return User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->get()
            ->map(function ($t) {

                $avg = Evaluation::whereHas('halaqa', function ($q) use ($t) {
                    $q->where('teacher_id', $t->id);
                })->avg('score');

                return [
                    'name' => $t->name,
                    'score' => round($avg ?? 0),
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take(5);
    }

    /*
    |-------------------------------------------------------
    | WEAK STUDENTS
    |-------------------------------------------------------
    */
    private function getWeakStudents($mosqueId)
    {
        return Student::where('mosque_id', $mosqueId)
            ->get()
            ->map(function ($s) {

                $avg = Evaluation::where('student_id', $s->id)->avg('score');

                return [
                    'name' => $s->first_name . ' ' . $s->last_name,
                    'score' => round($avg ?? 0),
                ];
            })
            ->filter(fn($s) => $s['score'] < 60)
            ->values()
            ->take(10);
    }

    /*
    |-------------------------------------------------------
    | ALERTS
    |-------------------------------------------------------
    */
    private function getAlerts($mosqueId)
    {
        $absent = Attendance::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->where('status', 'absent')
            ->whereMonth('date', Carbon::today()->month)
            ->count();

        return [
            'high_absence' => $absent,
        ];
    }

    /*
    |-------------------------------------------------------
    | PDF DATA (الحل النهائي للمشكلة)
    |-------------------------------------------------------
    */
    private function getSupervisorPdfData(int $mosqueId): array
    {
        $kpis = $this->getKpis($mosqueId);
        $halaqat = $this->getHalaqat($mosqueId);
        $topTeachers = $this->getTopTeachers($mosqueId);
        $weakStudents = $this->getWeakStudents($mosqueId);
        $alerts = $this->getAlerts($mosqueId);

        // الطلاب الأكثر غياباً
        $absentees = Student::where('mosque_id', $mosqueId)
            ->get()
            ->map(function ($s) {

                $absent = Attendance::where('student_id', $s->id)
                    ->where('status', 'absent')
                    ->whereMonth('date', Carbon::today()->month)
                    ->count();

                return [
                    'student_name' => $s->first_name . ' ' . $s->last_name,
                    'absent_days_this_month' => $absent,
                ];
            })
            ->filter(fn($x) => $x['absent_days_this_month'] > 0)
            ->sortByDesc('absent_days_this_month')
            ->take(10)
            ->values();

        // تحويل التنبيهات إلى مصفوفة من 'recent_activities' بصيغة متوافقة مع القالب
        $recentActivities = collect($alerts)->map(function ($value, $key) {
            // title human readable من المفتاح، يمكنك تخصيص النصوص حسب الحاجة
            $title = str_replace('_', ' ', ucfirst($key));

            return [
                'title' => $title,
                'description' => (string) $value, // أو نص أكثر وصفاً
                'time' => now()->format('Y-m-d H:i'),
            ];
        })->values()->toArray();

        return [
            'halaqat_count' => $kpis['halaqat'],
            'students_count' => $kpis['students'],
            'teachers_count' => $kpis['teachers'],
            'attendance_rate' => $kpis['attendance_rate'],
            'average_score' => $kpis['average_score'],

            'halaqat' => $halaqat,
            'top_teachers' => $topTeachers,
            'absentees' => $absentees,
            'recent_activities' => $recentActivities,
        ];

    }

    /*
    |-------------------------------------------------------
    | PDF GENERATOR
    |-------------------------------------------------------
    */
    public function generateSupervisorPdf(int $mosqueId, array $filters = []): string
    {
        $data = $this->getSupervisorPdfData($mosqueId);

        $html = view('dashboard::reports.supervisor', [
            ...$data,
            'supervisor' => auth()->user(),
            'generated_at' => now()->format('Y-m-d H:i'),
        ])->render();

        return Browsershot::html($html)
            ->setChromePath('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe')
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();
    }
}
