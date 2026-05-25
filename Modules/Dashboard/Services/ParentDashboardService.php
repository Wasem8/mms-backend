<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Modules\Education\Models\Student;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation;
use Spatie\Browsershot\Browsershot;

class ParentDashboardService
{
    /**
     * جلب قائمة الأبناء المرتبطين بولي الأمر مع ملخص سريع لكل ابن
     */
    public function getParentDashboardStats($parentId)
    {

        $children = Student::where('parent_id', $parentId)
            ->with(['halaqats:id,name'])
            ->get();

        if ($children->isEmpty()) {
            return [
                'has_children' => false,
                'message' => 'لا يوجد أبناء مسجلين تحت حسابك حالياً.'
            ];
        }

        $today = Carbon::today()->toDateString();

        $data = $children->map(function ($student) use ($today) {
            $todayAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('date', $today)
                ->value('status') ?? 'لم يرصد بعد';


            $lastEvaluation = Evaluation::where('student_id', $student->id)
                ->latest('evaluated_at')
                ->first();

            $monthAyahsCount = Evaluation::where('student_id', $student->id)
                ->whereMonth('evaluated_at', Carbon::today()->month)
                ->selectRaw('SUM(to_ayah - from_ayah + 1) as total')
                ->value('total') ?? 0;

            return [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
                'halaqa' => $student->halaqats->first()?->name ?? 'غير محدد',
                'today_attendance' => $todayAttendance,
                'month_progress' => $monthAyahsCount . ' آية المجموع التراكمي',
                'last_evaluation' => $lastEvaluation ? [
                    'surah' => $lastEvaluation->surah_name ?? 'غير محدد',
                    'score' => $lastEvaluation->score,
                    'date' => Carbon::parse($lastEvaluation->evaluated_at)->diffForHumans()
                ] : null
            ];
        });

        return [
            'has_children' => true,
            'children' => $data
        ];
    }

    public function generateParentReportPdf(int $parentId): string
    {
        $data = $this->getParentReportData($parentId);

        $html = view('dashboard::reports.parent', $data)->render();

        return Browsershot::html($html)
            ->setChromePath('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe')
            ->format('A4')
            ->margins(5,5,5,5)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->setDelay(3000)
            ->pdf();
    }

    private function getParentReportData($parentId): array
    {
        $children = Student::where('parent_id', $parentId)
            ->with(['halaqats:id,name'])
            ->get();

        if ($children->isEmpty()) {
            return [
                'has_children' => false
            ];
        }

        $today = Carbon::today()->toDateString();

        $childrenData = $children->map(function ($student) use ($today) {

            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

            $todayAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('date', $today)
                ->value('status') ?? 'لم يرصد';

            $attendancePercentage = Attendance::where('student_id', $student->id)
                ->whereMonth('date', Carbon::now()->month)
                ->selectRaw("
                ROUND(
                    (
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END)::decimal
                        /
                        NULLIF(COUNT(*),0)
                    ) * 100
                ) as percentage
            ")
                ->value('percentage') ?? 0;


            $lastEvaluation = Evaluation::where('student_id', $student->id)
                ->latest('evaluated_at')
                ->first();

            $monthAyahs = Evaluation::where('student_id', $student->id)
                ->whereMonth('evaluated_at', Carbon::today()->month)
                ->selectRaw('SUM(to_ayah - from_ayah + 1) as total')
                ->value('total') ?? 0;

            $evaluationsCount = Evaluation::where('student_id', $student->id)
                ->whereMonth('evaluated_at', Carbon::today()->month)
                ->count();

            $averageScore = Evaluation::where('student_id', $student->id)
                ->whereMonth('evaluated_at', Carbon::today()->month)
                ->avg('score');


            $performanceLevel = match (true) {
                $averageScore >= 90 => 'ممتاز',
                $averageScore >= 75 => 'جيد جداً',
                $averageScore >= 60 => 'جيد',
                default => 'بحاجة متابعة',
            };

            return [

                'name' => "{$student->first_name} {$student->last_name}",

                'halaqa' => $student->halaqats->first()?->name ?? 'غير محدد',

                'attendance' => $todayAttendance,

                'attendance_percentage' => $attendancePercentage,

                'month_progress' => $monthAyahs,

                'evaluations_count' => $evaluationsCount,

                'average_score' => round($averageScore ?? 0),

                'performance_level' => $performanceLevel,

                'last_evaluation' => $lastEvaluation,

            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Dashboard Summary Cards
        |--------------------------------------------------------------------------
        */

        $totalChildren = $childrenData->count();

        $totalAyahs = $childrenData->sum('month_progress');

        $avgAttendance = round(
            $childrenData->avg('attendance_percentage')
        );

        $avgScores = round(
            $childrenData->avg('average_score')
        );



        return [

            'has_children' => true,

            'parent' => auth()->user(),

            'generated_at' => now()->format('Y-m-d H:i'),

            'summary' => [
                'children_count' => $totalChildren,
                'total_ayahs' => $totalAyahs,
                'average_attendance' => $avgAttendance,
                'average_scores' => $avgScores,
            ],

            'children' => $childrenData,

        ];
    }
}
