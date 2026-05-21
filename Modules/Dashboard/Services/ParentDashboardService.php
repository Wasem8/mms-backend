<?php

namespace Modules\Dashboard\Services;

use Carbon\Carbon;
use Modules\Education\Models\Student;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Evaluation;

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
}
