<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Modules\Dashboard\Models\Report;
use Modules\Education\Models\{Halaqa, Student, Attendance, Evaluation};
use Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SupervisorDashboardService
{
    /*
    |-------------------------------------------------------
    | DASHBOARD (لوحة التحكم الرئيسية)
    |-------------------------------------------------------
    */
    public function getDashboard(int $mosqueId)
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
    | KPI (المؤشرات الرئيسية للشهر الحالي لتجنب الصفر الافتراضي)
    |-------------------------------------------------------
    */
    private function getKpis($mosqueId, $halaqaId = null)
    {
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        $studentQuery = Student::where('mosque_id', $mosqueId);
        if ($halaqaId) {
            $studentQuery->whereHas('halaqats', fn($q) => $q->where('halaqats.id', $halaqaId));
        }
        $students = $studentQuery->count();

        $teachers = User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->count();

        $halaqat = Halaqa::where('mosque_id', $mosqueId)->count();

        // حساب نسبة الحضور للشهر الحالي كاملاً لتعكس الأداء الحقيقي
        $attendanceQuery = Attendance::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        })
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear);

        if ($halaqaId) {
            $attendanceQuery->where('halaqa_id', $halaqaId);
        }

        $attendanceCount = $attendanceQuery->count();
        $attendanceRate = $attendanceCount
            ? round(($attendanceQuery->where('status', 'present')->count() / $attendanceCount) * 100)
            : 0;

        $evaluationQuery = Evaluation::whereHas('student', function ($q) use ($mosqueId) {
            $q->where('mosque_id', $mosqueId);
        });
        if ($halaqaId) {
            $evaluationQuery->where('halaqa_id', $halaqaId);
        }
        $avgScore = $evaluationQuery->avg('score');

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
    | HALAQAT (قائمة الحلقات)
    |-------------------------------------------------------
    */
    private function getHalaqat($mosqueId, $halaqaId = null)
    {
        $currentMonth = Carbon::today()->month;
        $currentYear = Carbon::today()->year;

        $query = Halaqa::with(['teacher', 'students'])->where('mosque_id', $mosqueId);

        if ($halaqaId) {
            $query->where('id', $halaqaId);
        }

        return $query->get()->map(function ($h) use ($currentMonth, $currentYear) {
            $attendance = Attendance::where('halaqa_id', $h->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->get();

            $present = $attendance->where('status', 'present')->count();
            $rate = $attendance->count() ? round(($present / $attendance->count()) * 100) : 0;
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
                    'name'  => $t->name,
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
                    'name'  => $s->first_name . ' ' . $s->last_name,
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
    | PDF GENERATOR RESPONSE
    |-------------------------------------------------------
    */
    public function generateSupervisorPdfResponse(int $mosqueId, array $filters = []): array
    {
        $supervisorId = auth()->id();
        $halaqaId = $filters['halaqa_id'] ?? null;

        // 1. نظام الكاش: البحث عن آخر تقرير منشأ للمشرف الحالي لتخفيف العبء
        $lastReport = Report::where('user_id', $supervisorId)
            ->where('type', 'supervisor_dashboard')
            ->latest()
            ->first();

        if ($lastReport && $lastReport->created_at->gt(now()->subDay())) {
            try {
                $signedUrl = $this->createSignedUrl($lastReport->storage_path);
                return [
                    'url'    => $signedUrl,
                    'cached' => true
                ];
            } catch (\Throwable $e) {
                $lastReport->delete();
            }
        }

        // 2. معالجة وتوليد الـ HTML والبيانات المفلترة لـ الـ Blade
        $data = $this->getSupervisorPdfData($mosqueId, $filters);

        $html = view('dashboard::reports.supervisor', [
            ...$data,
            'supervisor'   => auth()->user(),
            'generated_at' => now()->format('Y-m-d H:i'),
        ])->render();

        // 3. بناء وتوليد الـ PDF باستخدام محرك mPDF بشكل كامل
        $tempDir = '/tmp/mpdf_cache_supervisor';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            $remoteRegularUrl = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Regular.ttf';
            $remoteBoldUrl = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Bold.ttf';

            $localRegularPath = '/tmp/Cairo-Regular.ttf';
            $localBoldPath = '/tmp/Cairo-Bold.ttf';

            if (!file_exists($localRegularPath)) {
                file_put_contents($localRegularPath, @file_get_contents($remoteRegularUrl));
            }
            if (!file_exists($localBoldPath)) {
                file_put_contents($localBoldPath, @file_get_contents($remoteBoldUrl));
            }

            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir,
                'fontDir'       => array_merge($fontDirs, ['/tmp']),
                'fontdata'      => array_merge($fontData, [
                    'cairo' => [
                        'R'      => 'Cairo-Regular.ttf',
                        'B'      => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                    ]
                ]),
                'default_font' => 'cairo'
            ]);

            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');

        } catch (\Throwable $e) {
            \Log::error('mPDF SUPERVISOR VERCEL ERROR', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }

        // 4. تعيين مسمى ومسار الملف في Supabase Storage
        $fileName = 'supervisor-reports/' . $mosqueId . '/' . time() . '.pdf';

        // 5. الرفع السحابي للملف
        $this->uploadPdfToSupabase($pdfContent, $fileName);

        // 6. تسجيل العملية للكاش المستقبلي
        Report::create([
            'user_id'      => $supervisorId,
            'type'         => 'supervisor_dashboard',
            'storage_path' => $fileName,
        ]);

        return [
            'url'    => $this->createSignedUrl($fileName),
            'cached' => false,
        ];
    }

    private function getSupervisorPdfData(int $mosqueId, array $filters = []): array
    {
        $halaqaId = $filters['halaqa_id'] ?? null;

        $kpis = $this->getKpis($mosqueId, $halaqaId);
        $halaqat = $this->getHalaqat($mosqueId, $halaqaId);
        $topTeachers = $this->getTopTeachers($mosqueId);
        $weakStudents = $this->getWeakStudents($mosqueId);

        $absentees = Student::where('mosque_id', $mosqueId)
            ->get()
            ->map(function ($s) {
                $absent = Attendance::where('student_id', $s->id)
                    ->where('status', 'absent')
                    ->whereMonth('date', Carbon::today()->month)
                    ->count();

                return [
                    'student_name'           => $s->first_name . ' ' . $s->last_name,
                    'absent_days_this_month' => $absent,
                ];
            })
            ->filter(fn($x) => $x['absent_days_this_month'] > 0)
            ->sortByDesc('absent_days_this_month')
            ->take(5)
            ->values();

        return [
            'title'         => 'تقرير الأداء الرقابي الشامل لجمعية الحلقات',
            'date'          => now()->format('Y-m-d'),
            'stats'         => [
                'total_halaqats'  => $kpis['halaqat'],
                'total_students'  => $kpis['students'],
                'attendance_rate' => $kpis['attendance_rate'],
                'average_score'   => $kpis['average_score'],
            ],
            'halaqat'       => $halaqat,
            'top_teachers'  => $topTeachers,
            'weak_students' => $weakStudents,
            'absentees'     => $absentees,
        ];
    }

    /*
    |-------------------------------------------------------
    | SUPABASE STORAGE CONNECTION
    |-------------------------------------------------------
    */
    private function uploadPdfToSupabase(string $pdfContent, string $fileName): void
    {
        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.reports_bucket');
        $key     = config('services.supabase.key');

        $uploadUrl = $baseUrl . '/storage/v1/object/' . $bucket . '/' . $fileName;

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->withHeaders([
                'apikey'        => $key,
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/pdf',
            ])->withBody($pdfContent, 'application/pdf')
            ->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception('Supabase Supervisor PDF Upload Failed: ' . $response->body());
        }
    }

    private function createSignedUrl(string $fileName): string
    {
        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.reports_bucket');
        $key     = config('services.supabase.key');

        $response = Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->post($baseUrl . '/storage/v1/object/sign/' . $bucket . '/' . $fileName, [
            'expiresIn' => 3600
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to create signed URL for Supervisor: ' . $response->body());
        }

        return $baseUrl . '/storage/v1' . $response->json('signedURL');
    }
}
