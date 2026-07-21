<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Dashboard\Models\Report;
use Modules\Education\Models\{Halaqa, Student, Attendance, Evaluation};
use Modules\User\Models\User;

class SupervisorDashboardService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage
    ) {}

    /*
|-------------------------------------------------------
| FORMATTED DASHBOARD RESPONSE (صيغة معيارية محسّنة)
|-------------------------------------------------------
*/
    public function getFormattedDashboard(int $mosqueId, ?int $halaqaId = null): array
    {
        $filters = $halaqaId ? ['halaqa_id' => $halaqaId] : [];

        return [
            'status'      => true,
            'message'     => 'تم جلب إحصائيات لوحة التحكم للمشرف التربوي بنجاح',
            'data'        => [
                'cards'                 => $this->getCardsStats($mosqueId, $filters),
                'weekly_attendance'     => $this->getWeeklyAttendance($mosqueId, $filters),
                'quran_progress'        => $this->getQuranProgressCurve($mosqueId, $filters),
                'top_teachers'          => $this->getTopTeachersFormatted($mosqueId, $halaqaId),
                'absenteeism_report'    => $this->getAbsenteeismReportFormatted($mosqueId, $filters),
                'recent_activities'     => $this->getRecentActivitiesFormatted($mosqueId, $filters),
            ],
            'pagination'  => null,
        ];
    }

    private function getCardsStats(int $mosqueId, array $filters): array
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

    private function getWeeklyAttendance(int $mosqueId, array $filters): array
    {
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $last7Days->push(Carbon::today()->subDays($i));
        }

        return $last7Days->map(function ($date) use ($mosqueId, $filters) {
            $formattedDate = $date->toDateString();
            $query = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
                ->whereDate('date', $formattedDate);

            if (!empty($filters['halaqa_id'])) {
                $query->where('halaqa_id', $filters['halaqa_id']);
            }

            $stats = $query->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            return [
                'day' => $date->translatedFormat('l'),
                'percentage' => $stats->total > 0 ? round(($stats->present / $stats->total) * 100) : 0
            ];
        })->toArray();
    }

    private function getQuranProgressCurve(int $mosqueId, array $filters): array
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
                'average_score' => $stats->average_score ? round($stats->average_score) : 0,
                'total_evaluations' => (int)($stats->total_evaluations ?? 0)
            ];
        })->toArray();
    }

    private function getTopTeachersFormatted(int $mosqueId, ?int $halaqaId = null): array
    {
        $query = User::role('teacher')
            ->where('users.mosque_id', $mosqueId)
            ->join('halaqats', 'users.id', '=', 'halaqats.teacher_id')
            ->join('evaluations', 'halaqats.id', '=', 'evaluations.halaqa_id')
            ->select('users.id', 'users.name')
            ->selectRaw('COALESCE(SUM(evaluations.to_ayah - evaluations.from_ayah + 1), 0) as total_ayahs_reviewed')
            ->groupBy('users.id', 'users.name');

        if ($halaqaId) {
            $query->where('halaqats.id', $halaqaId);
        }

        return $query->orderByDesc('total_ayahs_reviewed')
            ->take(5)
            ->get()
            ->toArray();
    }

    private function getAbsenteeismReportFormatted(int $mosqueId, array $filters): array
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
            ])
            ->toArray();
    }

    private function getRecentActivitiesFormatted(int $mosqueId, array $filters): array
    {
        $studentQuery = Student::where('mosque_id', $mosqueId);
        $attendanceQuery = Attendance::whereHas('student', fn($q) => $q->where('mosque_id', $mosqueId))
            ->with(['student', 'halaqa', 'halaqa.teacher']);

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

        return $recentAttendance->concat($recentStudents)->sortByDesc('time')->values()->toArray();
    }


    /*
    |-------------------------------------------------------
    | PDF GENERATOR RESPONSE
    |-------------------------------------------------------
    */
    public function generateSupervisorPdfResponse(int $mosqueId, ?int $halaqaId = null): array
    {
        $currentUserId = auth()->id();
        $currentUser = auth()->user();

        $userRoleKey = $currentUser->hasRole('supervisor') ? 'supervisor' : 'mosque_manager';
        $userRoleTitle = $currentUser->hasRole('supervisor') ? 'المشرف التربوي' : 'مدير المسجد';

        // إضافة رقم الحلقة لرمز الكاش لضمان عدم تداخل التقرير العام بتقرير حلقة محددة
        $halaqaSuffix = $halaqaId ? "_halaqa_{$halaqaId}" : "_all";
        $cacheType = "mosque_{$mosqueId}_user_{$currentUserId}_{$userRoleKey}{$halaqaSuffix}";

        $lastReport = Report::where('type', $cacheType)
            ->latest()
            ->first();

        if ($lastReport && $lastReport->created_at->gt(now()->subDay())) {
            try {
                $signedUrl = $this->storage->createSignedUrl($lastReport->storage_path);
                return [
                    'url'    => $signedUrl,
                    'cached' => true
                ];
            } catch (\Throwable $e) {
                $lastReport->delete();
            }
        }

        $data = $this->getSupervisorPdfData($mosqueId, $halaqaId);

        $html = view('dashboard::reports.supervisor', [
            ...$data,
            'user_name'    => $currentUser->name,
            'user_role'    => $userRoleTitle,
            'generated_at' => now()->format('Y-m-d H:i'),
        ])->render();

        $pdfContent = $this->pdfGenerator->generate($html);
        $fileName = "mosque-reports/{$mosqueId}/{$userRoleKey}/user_{$currentUserId}_halaqa_{$halaqaId}_" . time() . '.pdf';
        $this->storage->uploadPdf($pdfContent, $fileName);

        Report::create([
            'user_id'      => $currentUserId,
            'type'         => $cacheType,
            'storage_path' => $fileName,
        ]);

        return [
            'url'    => $this->storage->createSignedUrl($fileName),
            'cached' => false,
        ];
    }

    /*
    |-------------------------------------------------------
    | بيانات التقرير الشامل
    |-------------------------------------------------------
    */
    private function getSupervisorPdfData(int $mosqueId, ?int $halaqaId = null): array
    {
        $kpis = $this->getKpis($mosqueId, $halaqaId);
        $halaqat = $this->getHalaqat($mosqueId, $halaqaId);

        return [
            'title'   => 'تقرير الأداء الرقابي ' . ($halaqaId ? 'للحلقة' : 'لحلقات المسجد'),
            'date'    => now()->format('Y-m-d'),
            'stats'   => [
                'total_halaqats'  => $kpis['halaqat'],
                'total_students'  => $kpis['students'],
                'attendance_rate' => $kpis['attendance_rate'],
                'average_score'   => $kpis['average_score'],
            ],
            'halaqat' => $halaqat,
        ];
    }

    /*
    |-------------------------------------------------------
    | KPI المؤشرات العامة
    |-------------------------------------------------------
    */
    private function getKpis(int $mosqueId, ?int $halaqaId = null)
    {
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        // إحصاء الطلاب بالاعتماد على الفلتر
        $studentsQuery = Student::where('mosque_id', $mosqueId);
        if ($halaqaId) {
            $studentsQuery->whereHas('halaqats', fn($q) => $q->where('halaqats.id', $halaqaId));
        }
        $students = $studentsQuery->count();

        // المعلمون للحلقة أو للمسجد
        $teachersQuery = User::role('teacher')->where('mosque_id', $mosqueId);
        if ($halaqaId) {
            $teachersQuery->whereHas('halaqats', fn($hq) => $hq->where('id', $halaqaId));
        }
        $teachers = $teachersQuery->count();

        // إحصاء الحلقات
        $halaqat = $halaqaId
            ? Halaqa::where('mosque_id', $mosqueId)->where('id', $halaqaId)->count()
            : Halaqa::where('mosque_id', $mosqueId)->count();

        // إحصائيات الحضور والغياب
        $attendanceQuery = Attendance::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->when($halaqaId, fn($q) => $q->where('halaqa_id', $halaqaId))
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear);

        $attendanceCount = (clone $attendanceQuery)->count();
        $presentCount = (clone $attendanceQuery)->where('status', 'present')->count();

        $attendanceRate = $attendanceCount
            ? round(($presentCount / $attendanceCount) * 100)
            : 0;

        // متوسط التقييمات
        $avgScore = Evaluation::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->when($halaqaId, fn($q) => $q->where('halaqa_id', $halaqaId))
            ->avg('score');

        return [
            'students'        => $students,
            'teachers'        => $teachers,
            'halaqat'         => $halaqat,
            'attendance_rate' => $attendanceRate,
            'average_score'   => round($avgScore ?? 0),
        ];
    }

    /*
    |-------------------------------------------------------
    | قائمة الحلقات
    |-------------------------------------------------------
    */
    private function getHalaqat(int $mosqueId, ?int $halaqaId = null)
    {
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        return Halaqa::with(['teacher', 'students'])
            ->where('mosque_id', $mosqueId)
            ->when($halaqaId, fn($q) => $q->where('id', $halaqaId))
            ->get()
            ->map(function ($h) use ($currentMonth, $currentYear) {
                $totalAttendance = Attendance::where('halaqa_id', $h->id)
                    ->whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->count();

                $presentAttendance = $totalAttendance
                    ? Attendance::where('halaqa_id', $h->id)
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
                        ->where('status', 'present')
                        ->count()
                    : 0;

                $rate = $totalAttendance ? round(($presentAttendance / $totalAttendance) * 100) : 0;
                $avg = Evaluation::where('halaqa_id', $h->id)->avg('score');

                return [
                    'id'              => $h->id,
                    'name'            => $h->name,
                    'teacher'         => $h->teacher?->name ?? 'غير مسند',
                    'students_count'  => $h->students->count(),
                    'attendance_rate' => $rate,
                    'average_score'   => round($avg ?? 0),
                    'created_at'      => $h->created_at
                ];
            })
            ->toArray();
    }

    /*
    |-------------------------------------------------------
    | أفضل المعلمين
    |-------------------------------------------------------
    */
    private function getTopTeachers(int $mosqueId, ?int $halaqaId = null)
    {
        return User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->when($halaqaId, function ($q) use ($halaqaId) {
                // 🎯 استبدال 'halaqats.id' بـ 'id' فقط دون كتابة اسم الجدول يدوياً
                $q->whereHas('halaqats', fn($hq) => $hq->where('id', $halaqaId));
            })
            ->get()
            ->map(function ($t) use ($halaqaId) {
                $avg = Evaluation::whereHas('halaqa', function ($q) use ($t) {
                    $q->where('teacher_id', $t->id);
                })
                    ->when($halaqaId, fn($q) => $q->where('halaqa_id', $halaqaId))
                    ->avg('score');

                return ['name' => $t->name, 'score' => round($avg ?? 0)];
            })
            ->sortByDesc('score')->values()->take(5)->toArray();
    }

    /*
    |-------------------------------------------------------
    | الطلاب الأقل أداءً
    |-------------------------------------------------------
    */
    private function getWeakStudents(int $mosqueId, ?int $halaqaId = null)
    {
        return Student::where('mosque_id', $mosqueId)
            ->when($halaqaId, function ($q) use ($halaqaId) {
                $q->whereHas('halaqats', fn($hq) => $hq->where('halaqats.id', $halaqaId));
            })
            ->get()
            ->map(function ($s) use ($halaqaId) {
                $avg = Evaluation::where('student_id', $s->id)
                    ->when($halaqaId, fn($q) => $q->where('halaqa_id', $halaqaId))
                    ->avg('score');

                return ['name' => $s->first_name . ' ' . $s->last_name, 'score' => round($avg ?? 0)];
            })
            ->filter(fn($s) => $s['score'] < 60 && $s['score'] > 0)
            ->sortBy('score')->values()->take(10)->toArray();
    }

    /*
    |-------------------------------------------------------
    | التنبيهات
    |-------------------------------------------------------
    */
    private function getAlerts(int $mosqueId, ?int $halaqaId = null)
    {
        $absent = Attendance::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->when($halaqaId, fn($q) => $q->where('halaqa_id', $halaqaId))
            ->where('status', 'absent')
            ->whereMonth('date', Carbon::today()->month)
            ->count();

        return ['high_absence' => $absent];
    }
}
