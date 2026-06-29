<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
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
    | DASHBOARD (لوحة التحكم الرئيسية للفرونت إند - تبقى كما هي)
    |-------------------------------------------------------
    */
    public function getDashboard(int $mosqueId): array
    {
        return [
            'kpis'          => $this->getKpis($mosqueId),
            'halaqat'       => $this->getHalaqat($mosqueId),
            'top_teachers'  => $this->getTopTeachers($mosqueId),
            'weak_students' => $this->getWeakStudents($mosqueId),
            'alerts'        => $this->getAlerts($mosqueId),
        ];
    }

    /*
    |-------------------------------------------------------
    | PDF GENERATOR RESPONSE (تقرير مقتضب ومثالي للأداء)
    |-------------------------------------------------------
    */
    public function generateSupervisorPdfResponse(int $mosqueId): array
    {
        $currentUserId = auth()->id();
        $currentUser = auth()->user();

        // تحديد دور المستخدم الحالي بدقة لاستخدامه في الكاش وفي ملف الـ Blade
        $userRoleKey = $currentUser->hasRole('supervisor') ? 'supervisor' : 'mosque_manager';
        $userRoleTitle = $currentUser->hasRole('supervisor') ? 'المشرف التربوي' : 'مدير المسجد';

        // 1. نظام كاش منفصل ومحدد: نربط الكاش بـ (رقم المسجد + رقم المستخدم + دور المستخدم)
        // بهذه الطريقة لا يمكن للمشرف والمدير مشاركة نفس ملف الكاش نهائياً
        $cacheType = "mosque_{$mosqueId}_user_{$currentUserId}_{$userRoleKey}";

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

        // 2. معالجة وتجميع بيانات التقرير العام للمسجد
        $data = $this->getSupervisorPdfData($mosqueId);

        // 3. توليد الـ HTML ونمرر اسم المستخدم الحالي ودوره الفعلي للغلاف
        $html = view('dashboard::reports.supervisor', [
            ...$data,
            'user_name'    => $currentUser->name,
            'user_role'    => $userRoleTitle,
            'generated_at' => now()->format('Y-m-d H:i'),
        ])->render();

        // 4. توليد الـ PDF والرفع السحابي (نظمنا المجلدات سحابياً أيضاً حسب الدور والمستخدم)
        $pdfContent = $this->pdfGenerator->generate($html);
        $fileName = "mosque-reports/{$mosqueId}/{$userRoleKey}/user_{$currentUserId}_" . time() . '.pdf';
        $this->storage->uploadPdf($pdfContent, $fileName);

        // 5. تسجيل العملية في جدول التقارير بالـ Cache Type الذكي والمنفصل
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
    | تجميع بيانات التقرير الشامل (تم تنظيفها من الزوائد)
    |-------------------------------------------------------
    */
    private function getSupervisorPdfData(int $mosqueId): array
    {
        $kpis = $this->getKpis($mosqueId);
        $halaqat = $this->getHalaqat($mosqueId);

        return [
            'title'   => 'تقرير الأداء الرقابي الشامل لحلقات المسجد',
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
    | KPI المؤشرات العامة للمسجد
    |-------------------------------------------------------
    */
    private function getKpis($mosqueId)
    {
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        $students = Student::where('mosque_id', $mosqueId)->count();

        $teachers = User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->count();

        $halaqat = Halaqa::where('mosque_id', $mosqueId)->count();

        $attendanceQuery = Attendance::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear);

        $attendanceCount = $attendanceQuery->count();
        $attendanceRate = $attendanceCount
            ? round(($attendanceQuery->where('status', 'present')->count() / $attendanceCount) * 100)
            : 0;

        $avgScore = Evaluation::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })->avg('score');

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
    | HALAQAT قائمة أداء الحلقات بالكامل
    |-------------------------------------------------------
    */
    private function getHalaqat($mosqueId)
    {
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        return Halaqa::with(['teacher', 'students'])
            ->where('mosque_id', $mosqueId)
            ->get()
            ->map(function ($h) use ($currentMonth, $currentYear) {
                // تحسين الأداء: نقوم بحساب الحضور مباشرة بدون سحب السجلات كاملة للذاكرة
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
    | دالات مساعدة مخصصة للوحة التحكم (Dashboard) فقط
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

                return ['name' => $t->name, 'score' => round($avg ?? 0)];
            })
            ->sortByDesc('score')->values()->take(5)->toArray();
    }

    private function getWeakStudents($mosqueId)
    {
        return Student::where('mosque_id', $mosqueId)
            ->get()
            ->map(function ($s) {
                $avg = Evaluation::where('student_id', $s->id)->avg('score');
                return ['name' => $s->first_name . ' ' . $s->last_name, 'score' => round($avg ?? 0)];
            })
            ->filter(fn($s) => $s['score'] < 60 && $s['score'] > 0)
            ->sortBy('score')->values()->take(10)->toArray();
    }

    private function getAlerts($mosqueId)
    {
        $absent = Attendance::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->where('status', 'absent')
            ->whereMonth('date', Carbon::today()->month)
            ->count();

        return ['high_absence' => $absent];
    }
}
