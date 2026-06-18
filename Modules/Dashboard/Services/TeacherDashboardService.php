<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Dashboard\Models\Report;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\AttendanceExcuse;
use Modules\Education\Models\Evaluation;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\User\Models\User;

class TeacherDashboardService
{
    // حقن الخدمات المنفصلة تلقائياً عبر الـ Constructor
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage
    ) {}

    /**
     * جلب بيانات تهيئة لوحة تحكم المعلم (API للموبايل والفرونت إند)
     */
    public function getTeacherBootstrap($teacherId)
    {
        $halaqat = Halaqa::where('teacher_id', $teacherId)
            ->select('id', 'name')
            ->get();

        $halaqaIds = $halaqat->pluck('id');

        /**
         * 🔥 Load all students in all halaqat (one query)
         */
        $students = DB::table('halaqa_student')
            ->join('students', 'students.id', '=', 'halaqa_student.student_id')
            ->whereIn('halaqa_student.halaqa_id', $halaqaIds)
            ->select(
                'students.id',
                'students.first_name',
                'students.last_name',
                'halaqa_student.halaqa_id'
            )
            ->get()
            ->groupBy('halaqa_id');

        /**
         * 🔥 Load evaluations (last 5 + last passage)
         */
        $evaluations = Evaluation::whereIn('halaqa_id', $halaqaIds)
            ->whereIn('student_id', $students->flatten()->pluck('id')->unique())
            ->orderBy('evaluated_at', 'desc')
            ->get()
            ->groupBy('student_id');

        /**
         * 🔥 Load absences (30 days)
         */
        $absences = AttendanceExcuse::whereIn('halaqa_id', $halaqaIds)
            ->where('status', 'approved')
            ->where('absence_date', '>=', now()->subDays(30))
            ->get()
            ->groupBy('student_id');

        $rosters = [];

        foreach ($halaqat as $halaqa) {
            $rosters[$halaqa->id] = ($students[$halaqa->id] ?? collect())
                ->map(function ($student) use ($evaluations, $absences) {
                    $studentEvaluations = $evaluations[$student->id] ?? collect();
                    $lastEvaluation = $studentEvaluations->first();

                    $recentScores = $studentEvaluations
                        ->take(5)
                        ->pluck('score')
                        ->reverse()
                        ->values();

                    $recentAbsences = isset($absences[$student->id])
                        ? $absences[$student->id]->count()
                        : 0;

                    return [
                        'id' => $student->id,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'last_passage' => $lastEvaluation ? [
                            'surah_name' => $lastEvaluation->surah_name,
                            'from_ayah' => $lastEvaluation->from_ayah,
                            'to_ayah' => $lastEvaluation->to_ayah,
                            'evaluated_at' => $lastEvaluation->evaluated_at,
                        ] : null,
                        'recent_scores' => $recentScores,
                        'recent_absences' => $recentAbsences,
                    ];
                });
        }

        /**
         * 🔥 Pending Excuses
         */
        $pendingExcuses = AttendanceExcuse::with([
            'student:id,first_name,last_name',
            'parent:id,name',
            'halaqa:id,name',
        ])
            ->whereHas('halaqa', fn ($q) => $q->where('teacher_id', $teacherId))
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
        $halaqa = Halaqa::where('teacher_id', $teacherId)->first();

        if (!$halaqa) {
            return [
                'has_halaqa' => false,
                'message' => 'لم يتم تعيينك في أي حلقة بعد.'
            ];
        }

        $today = Carbon::today()->toDateString();
        $currentMonth = Carbon::today()->month;

        $totalStudents = Student::whereHas('halaqats', fn($q) => $q->where('halaqats.id', $halaqa->id))->count();

        $todayAttendance = Attendance::where('halaqa_id', $halaqa->id)
            ->whereDate('date', $today)
            ->get();

        $presentCount = $todayAttendance->where('status', 'present')->count();
        $attendanceRate = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;

        $evaluatedTodayCount = Evaluation::where('halaqa_id', $halaqa->id)
            ->whereDate('evaluated_at', $today)
            ->distinct('student_id')
            ->count();

        $totalAyahsThisMonth = Evaluation::where('halaqa_id', $halaqa->id)
            ->whereMonth('evaluated_at', $currentMonth)
            ->selectRaw('SUM(to_ayah - from_ayah + 1) as total_ayahs')
            ->value('total_ayahs') ?? 0;

        $frequentAbsentees = Attendance::where('halaqa_id', $halaqa->id)
            ->where('status', 'absent')
            ->whereMonth('date', $currentMonth)
            ->with('student:id,first_name,last_name')
            ->select('student_id', DB::raw('COUNT(*) as absent_days'))
            ->groupBy('student_id')
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
                'frequent_absentees' => $frequentAbsentees
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
                    'time'         => Carbon::parse($ev->evaluated_at)->diffForHumans()
                ];
            });
    }

    /*
    |-------------------------------------------------------
    | PDF GENERATOR RESPONSE (توليد تقرير أداء المعلم الفخم)
    |-------------------------------------------------------
    */
    public function generateTeacherReportPdf(int $teacherId): array
    {
        // 1. نظام كاش منفصل ومحمي لكل معلم على حدة
        $cacheType = "teacher_dashboard_user_{$teacherId}";

        $lastReport = Report::where('user_id', $teacherId)
            ->where('type', $cacheType)
            ->latest()
            ->first();

        if ($lastReport && $lastReport->created_at->gt(now()->subDay())) {
            try {
                // استدعاء خدمة الـ الرفع المنفصلة لجلب الرابط الموقع
                $signedUrl = $this->storage->createSignedUrl($lastReport->storage_path);

                return [
                    'url'    => $signedUrl,
                    'cached' => true
                ];
            } catch (\Throwable $e) {
                $lastReport->delete();
            }
        }

        // 2. جلب البيانات وتحويل الـ Blade إلى HTML
        $data = $this->getData($teacherId);

        if (!$data['has_halaqa']) {
            throw new \Exception('لا يمكن توليد التقرير لعدم وجود حلقة مسندة لهذا المعلم.');
        }

        $html = view('dashboard::reports.teacher', $data)->render();

        // 3. استخدام الخدمة الموحدة النظيفة لتوليد محتوى الـ PDF
        $pdfContent = $this->pdfGenerator->generate($html);

        // 4. تجهيز مسار الحفظ السحابي
        $fileName = "teacher-reports/{$teacherId}/general_" . time() . '.pdf';

        // 5. رفع الملف باستخدام خدمة SupabaseStorageService
        $this->storage->uploadPdf($pdfContent, $fileName);

        // 6. حفظ السجل بقاعدة البيانات للكاش المستقبلي
        Report::create([
            'user_id'      => $teacherId,
            'type'         => $cacheType,
            'storage_path' => $fileName,
        ]);

        return [
            'url'    => $this->storage->createSignedUrl($fileName),
            'cached' => false,
        ];
    }

    /**
     * تجميع ومعالجة بيانات تقرير المعلم
     */
    private function getData($teacherId)
    {
        $teacher = User::find($teacherId);

        $halaqa = Halaqa::where('teacher_id', $teacherId)->first();

        if (!$halaqa) {
            return [
                'has_halaqa' => false
            ];
        }

        $today = Carbon::today();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // جلب طلاب الحلقة
        $halaqaStudents = Student::whereHas('halaqats', function ($q) use ($halaqa) {
            $q->where('halaqats.id', $halaqa->id);
        })->get();

        // بناء تفاصيل أداء كل طالب
        $students = $halaqaStudents->map(function ($student) use ($today, $halaqa, $currentMonth, $currentYear) {
            $attendanceToday = Attendance::where('student_id', $student->id)
                ->where('halaqa_id', $halaqa->id)
                ->whereDate('date', $today)
                ->value('status') ?? 'لم يرصد';

            // حساب نسبة الحضور للشهر الحالي بطريقة PostgreSQL الحصينة
            $attendancePercentage = Attendance::where('student_id', $student->id)
                ->where('halaqa_id', $halaqa->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->selectRaw("
                    ROUND(
                        (
                            SUM(CASE WHEN status='present' THEN 1 ELSE 0 END)::decimal
                            /
                            NULLIF(COUNT(*), 0)
                        ) * 100
                    ) as percentage
                ")
                ->value('percentage') ?? 0;

            $averageScore = round(
                Evaluation::where('student_id', $student->id)
                    ->where('halaqa_id', $halaqa->id)
                    ->avg('score') ?? 0
            );

            $performance = match (true) {
                $averageScore >= 90 => 'ممتاز',
                $averageScore >= 75 => 'جيد جداً',
                $averageScore >= 60 => 'جيد',
                default => 'بحاجة متابعة',
            };

            return [
                'name'                  => "{$student->first_name} {$student->last_name}",
                'attendance'            => $attendanceToday,
                'attendance_percentage' => (int) $attendancePercentage,
                'average_score'         => (int) $averageScore,
                'performance'           => $performance,
            ];
        });

        // حساب نسبة الحضور العامة للحلقة هذا الشهر
        $totalMonthlyRecords = Attendance::where('halaqa_id', $halaqa->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->count();

        $attendanceRate = 0;
        if ($totalMonthlyRecords > 0) {
            $presentMonthlyCount = Attendance::where('halaqa_id', $halaqa->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'present')
                ->count();

            $attendanceRate = round(($presentMonthlyCount / $totalMonthlyRecords) * 100);
        }

        // آخر 10 تقييمات
        $evaluations = Evaluation::where('halaqa_id', $halaqa->id)
            ->with('student')
            ->latest('evaluated_at')
            ->take(10)
            ->get();

        $averageScore = round($students->avg('average_score') ?? 0);
        $studentsCount = $halaqaStudents->count();

        return [
            'has_halaqa'  => true,
            'teacher'     => $teacher,
            'halaqa'      => $halaqa,
            'cards' => [
                'students'          => $studentsCount,
                'attendance_rate'   => (int) $attendanceRate,
                'evaluations_today' => $evaluations->count(),
                'average_score'     => (int) $averageScore,
            ],
            'students'    => $students,
            'evaluations' => $evaluations,
        ];
    }
}
