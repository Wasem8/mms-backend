<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\Log;
use Modules\Dashboard\Models\Report;
use Modules\User\Models\User;
use Modules\Education\Models\Student;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation;

class ParentDashboardService
{
    public function __construct(
        private readonly PdfGeneratorService $pdfGenerator,
        private readonly SupabaseStorageService $storage,
    ) {}

    public function getParentDashboardStats(int $parentId): array
    {
        return $this->buildParentData($parentId);
    }

    public function generateParentReportPdf(User $parent): array
    {
        $parentId = $parent->id;

        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        */

        $lastReport = Report::where('user_id', $parentId)
            ->where('type', 'parent_dashboard')
            ->latest()
            ->first();

        if (
            $lastReport &&
            $lastReport->created_at->gt(now()->subDay())
        ) {
            try {
                return [
                    'url' => $this->storage->createSignedUrl(
                        $lastReport->storage_path
                    ),
                    'cached' => true,
                ];
            } catch (\Throwable $e) {
                Log::warning(
                    'Parent report cached file unavailable',
                    [
                        'parent_id' => $parentId,
                        'error' => $e->getMessage(),
                    ]
                );

                $lastReport->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 1. جلب بيانات التقرير فقط
        |--------------------------------------------------------------------------
        */

        $data = $this->getParentReportData($parentId);

        if (!$data['has_children']) {
            throw new \Exception(
                'لا يوجد أبناء مرتبطون بهذا الحساب.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Blade → HTML
        |--------------------------------------------------------------------------
        */

        $html = view(
            'dashboard::reports.parent',
            $data
        )->render();

        /*
        |--------------------------------------------------------------------------
        | 3. HTML → PDF
        |--------------------------------------------------------------------------
        */

        $pdfContent = $this->pdfGenerator->generate($html);

        /*
        |--------------------------------------------------------------------------
        | 4. Upload to Supabase
        |--------------------------------------------------------------------------
        */

        $fileName =
            'parent-reports/' .
            $parentId .
            '/' .
            now()->format('Y-m-d_H-i-s') .
            '.pdf';

        $this->storage->uploadPdf(
            $pdfContent,
            $fileName
        );

        /*
        |--------------------------------------------------------------------------
        | 5. Cache record
        |--------------------------------------------------------------------------
        */

        Report::create([
            'user_id' => $parentId,
            'type' => 'parent_dashboard',
            'storage_path' => $fileName,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 6. Signed URL
        |--------------------------------------------------------------------------
        */

        return [
            'url' => $this->storage->createSignedUrl($fileName),
            'cached' => false,
        ];
    }

    private function getParentReportData(int $parentId): array
    {
        /*
        |--------------------------------------------------------------------------
        | الطلاب
        |--------------------------------------------------------------------------
        */

        $children = Student::where('parent_id', $parentId)
            ->with([
                'halaqats:id,name',
            ])
            ->get();

        if ($children->isEmpty()) {
            return [
                'has_children' => false,
            ];
        }

        $studentIds = $children->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Attendance - Query واحدة
        |--------------------------------------------------------------------------
        */

        $attendanceRecords = Attendance::whereIn(
            'student_id',
            $studentIds
        )
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get([
                'student_id',
                'date',
                'status',
            ])
            ->groupBy('student_id');

        /*
        |--------------------------------------------------------------------------
        | Evaluations - Query واحدة
        |--------------------------------------------------------------------------
        */

        $evaluationRecords = Evaluation::whereIn(
            'student_id',
            $studentIds
        )
            ->whereMonth('evaluated_at', now()->month)
            ->whereYear('evaluated_at', now()->year)
            ->orderByDesc('evaluated_at')
            ->get([
                'id',
                'student_id',
                'surah_name',
                'from_ayah',
                'to_ayah',
                'score',
                'evaluated_at',
            ])
            ->groupBy('student_id');

        /*
        |--------------------------------------------------------------------------
        | Build students
        |--------------------------------------------------------------------------
        */

        $childrenData = $children->map(function ($student) use (
            $attendanceRecords,
            $evaluationRecords
        ) {

            $attendance =
                $attendanceRecords->get(
                    $student->id,
                    collect()
                );

            $todayAttendance = $attendance->first(
                fn ($record) =>
                \Carbon\Carbon::parse($record->date)
                    ->isToday()
            );

            $attendanceTotal = $attendance->count();

            $attendancePresent = $attendance
                ->where('status', 'present')
                ->count();

            $attendancePercentage =
                $attendanceTotal > 0
                    ? round(
                    ($attendancePresent / $attendanceTotal) * 100
                )
                    : 0;

            $evaluations =
                $evaluationRecords->get(
                    $student->id,
                    collect()
                );

            $lastEvaluation =
                $evaluations->first();

            $monthAyahs =
                $evaluations->sum(function ($evaluation) {

                    if (
                        $evaluation->from_ayah === null ||
                        $evaluation->to_ayah === null
                    ) {
                        return 0;
                    }

                    return max(
                        0,
                        $evaluation->to_ayah -
                        $evaluation->from_ayah +
                        1
                    );
                });

            $averageScore =
                round(
                    $evaluations->avg('score') ?? 0
                );

            $performanceLevel = match (true) {
                $averageScore >= 90 => 'ممتاز',
                $averageScore >= 75 => 'جيد جداً',
                $averageScore >= 60 => 'جيد',
                default => 'بحاجة متابعة',
            };

            return [
                'name' =>
                    "{$student->first_name} {$student->last_name}",

                'halaqa' =>
                    $student->halaqats?->name
                    ?? 'غير محدد',

                'attendance' =>
                    $todayAttendance?->status
                    ?? 'لم يرصد',

                'attendance_percentage' =>
                    $attendancePercentage,

                'month_progress' =>
                    $monthAyahs,

                'evaluations_count' =>
                    $evaluations->count(),

                'average_score' =>
                    $averageScore,

                'performance_level' =>
                    $performanceLevel,

                'last_evaluation' =>
                    $lastEvaluation,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        return [
            'has_children' => true,

            'parent' => User::find($parentId),

            'generated_at' =>
                now()->format('Y-m-d H:i'),

            'summary' => [
                'children_count' =>
                    $childrenData->count(),

                'total_ayahs' =>
                    $childrenData->sum('month_progress'),

                'average_attendance' =>
                    round(
                        $childrenData->avg(
                            'attendance_percentage'
                        ) ?? 0
                    ),

                'average_scores' =>
                    round(
                        $childrenData->avg(
                            'average_score'
                        ) ?? 0
                    ),
            ],

            'children' => $childrenData,
        ];
    }

    private function buildParentData(int $parentId): array
    {
        return $this->getParentReportData($parentId);
    }
}
