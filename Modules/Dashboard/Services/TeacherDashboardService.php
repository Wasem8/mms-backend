<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Dashboard\Models\Report;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\AttendanceExcuse;
use Modules\Education\Models\Evaluation;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\User\Models\User;


class TeacherDashboardService
{

    public function getTeacherBootstrap($teacherId)
    {
        $halaqat = Halaqa::where('teacher_id', $teacherId)
            ->select('id', 'name')
            ->get();

        $halaqaIds = $halaqat->pluck('id');

        /**
         * 🔥 Load all students in all halaqat (one query)
         */
        $students = \DB::table('halaqa_student')
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
        $evaluations = \Modules\Education\Models\Evaluation::whereIn('halaqa_id', $halaqaIds)
            ->whereIn('student_id', $students->flatten()->pluck('id')->unique())
            ->orderBy('evaluated_at', 'desc')
            ->get()
            ->groupBy('student_id');

        /**
         * 🔥 Load absences (30 days)
         */
        $absences = \Modules\Education\Models\AttendanceExcuse::whereIn('halaqa_id', $halaqaIds)
            ->where('status', 'approved')
            ->where('absence_date', '>=', now()->subDays(30))
            ->get()
            ->groupBy('student_id');

        /**
         * =========================
         * ROOSTERS BUILD
         * =========================
         */
        $rosters = [];

        foreach ($halaqat as $halaqa) {

            $rosters[$halaqa->id] = ($students[$halaqa->id] ?? collect())
                ->map(function ($student) use ($evaluations, $absences) {

                    $studentEvaluations =
                        $evaluations[$student->id] ?? collect();

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


    public function generateTeacherReportPdf(int $teacherId): array
    {
        // 1. نظام الكاش: التحقق من وجود تقرير تم إنشاؤه خلال آخر 24 ساعة للحفاظ على الأداء
        $lastReport = Report::where('user_id', $teacherId)
            ->where('type', 'teacher_dashboard')
            ->latest()
            ->first();

        if (
            $lastReport &&
            $lastReport->created_at->gt(
                now()->subDay()
            )
        ) {
            try {
                $signedUrl = $this->createSignedUrl(
                    $lastReport->storage_path
                );

                return [
                    'url' => $signedUrl,
                    'cached' => true
                ];
            } catch (\Throwable $e) {
                $lastReport->delete();
            }
        }

        // 2. جلب البيانات وتحويل الـ Blade إلى HTML
        $data = $this->getData($teacherId);
        $html = view('dashboard::reports.teacher', $data)->render();

        // 🎯 حل مشكلة الـ Read-only في Vercel
        $tempDir = '/tmp/mpdf_cache_teacher';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            // 🎯 تحميل خط القاهرة ديناميكياً إلى المجلد المؤقت بالسيرفر /tmp
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

            // تهيئة mPDF
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
            $pdfContent = $mpdf->Output('', 'S'); // استخراج المحتوى كـ Binary String للرفع

        } catch (\Throwable $e) {
            \Log::error('mPDF TEACHER VERCEL ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // 3. تجهيز مسار واسم الملف الفريد على Supabase
        $fileName =
            'teacher-reports/' .
            $teacherId . '/' .
            time() . '.pdf';

        // 4. رفع الملف السحابي إلى الـ Bucket الخاص بـ Supabase
        $this->uploadPdfToSupabase(
            $pdfContent,
            $fileName
        );

        // 5. حفظ السجل بقاعدة البيانات للكاش المستقبلي
            Report::create([
            'user_id'      => $teacherId,
            'type'         => 'teacher_dashboard',
            'storage_path' => $fileName,
        ]);

        // 6. إرجاع الرابط الموقّع ومؤشر الكاش
        return [
            'url' => $this->createSignedUrl(
                $fileName
            ),
            'cached' => false,
        ];
    }

    /**
     * 💡 تأكد من وجود ميثود الرفع والـ Signed URL داخل السيرفس أو وراثة الـ Trait الخاص بها
     */
    private function uploadPdfToSupabase(
        string $pdfContent,
        string $fileName
    ): void {

        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.reports_bucket');
        $key     = config('services.supabase.key');

        $uploadUrl =
            $baseUrl .
            '/storage/v1/object/' .
            $bucket .
            '/' .
            $fileName;

        // 🎯 التعديل: محاولة الاتصال 3 مرات بين كل مرة ثانية واحدة، وزيادة وقت الانتظار لـ 30 ثانية
        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->withHeaders([
                'apikey'       => $key,
                'Authorization'=> 'Bearer ' . $key,
                'Content-Type' => 'application/pdf',
            ])->withBody(
                $pdfContent,
                'application/pdf'
            )->post($uploadUrl);

        if (! $response->successful()) {
            throw new \Exception(
                'Supabase PDF Upload Failed: ' .
                $response->body()
            );
        }
    }

    private function createSignedUrl(string $fileName): string
    {
        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.reports_bucket');
        $key     = config('services.supabase.key');

        $response = Http::withHeaders([
            'apikey'       => $key,
            'Authorization'=> 'Bearer ' . $key,
        ])->post(
            $baseUrl .
            '/storage/v1/object/sign/' .
            $bucket .
            '/' .
            $fileName,
            [
                'expiresIn' => 3600 // ساعة
            ]
        );

        if (! $response->successful()) {
            throw new \Exception(
                'Failed to create signed URL: ' .
                $response->body()
            );
        }

        return $baseUrl .
            '/storage/v1' .
            $response->json('signedURL');
    }
    private function getData($teacherId)
    {
        // جلب المعلم الممرر للدالة بدلاً من auth() لضمان عمل الـ Jobs أو الـ كاش بشكل صحيح
        $teacher = User::find($teacherId) ?? auth()->user();

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
        $currentMonth = now()->month;
        $currentYear = now()->year;

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
                $halaqa,
                $currentMonth,
                $currentYear
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

                // حساب نسبة الحضور للشهر الحالي بالطريقة المتوافقة مع PostgreSQL و MySQL
                $attendancePercentage =
                    Attendance::where(
                        'student_id',
                        $student->id
                    )
                        ->where(
                            'halaqa_id',
                            $halaqa->id
                        )
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
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
                                NULLIF(COUNT(*), 0)
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
                    'name' => "{$student->first_name} {$student->last_name}",
                    'attendance' => $attendanceToday,
                    'attendance_percentage' => (int) $attendancePercentage,
                    'average_score' => (int) $averageScore,
                    'performance' => $performance,
                ];
            }
        );

        /*
        |-----------------------------------
        | Attendance summary (تعديل الحسبة العامة للشهر الحالي)
        |-----------------------------------
        */
        // جلب الحضور العام لكل طلاب الحلقة خلال هذا الشهر بالكامل لتجنب صفر اليوم الحالي
        $totalMonthlyRecords = Attendance::where('halaqa_id', $halaqa->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->count();

        if ($totalMonthlyRecords > 0) {
            $presentMonthlyCount = Attendance::where('halaqa_id', $halaqa->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'present')
                ->count();

            $attendanceRate = round(($presentMonthlyCount / $totalMonthlyRecords) * 100);
        } else {
            $attendanceRate = 0;
        }

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

        $studentsCount = $halaqaStudents->count();

        return [
            'has_halaqa' => true,
            'teacher' => $teacher,
            'halaqa' => $halaqa,
            'cards' => [
                'students' => $studentsCount,
                'attendance_rate' => (int) $attendanceRate, // ستظهر النسبة الحقيقية الآن للشهر
                'evaluations_today' => $evaluations->count(),
                'average_score' => (int) $averageScore,
            ],
            'students' => $students,
            'evaluations' => $evaluations,
        ];
    }
}
