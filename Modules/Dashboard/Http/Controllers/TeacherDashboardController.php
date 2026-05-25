<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\TeacherDashboardService;

class TeacherDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(TeacherDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * عرض بيانات داشبورد المعلم
     */
    public function index(Request $request)
    {
        $teacherId = auth()->id();

        $stats = $this->dashboardService->getTeacherStats($teacherId);

        return ApiResponse::success($stats,'تم جلب إحصائيات لوحة التحكم للمعلم بنجاح');

    }

    public function exportPdf(Request $request)
    {
        $teacherId = auth()->id();

        $pdfContent = $this->dashboardService->generateTeacherReportPdf($teacherId);

        return response()->stream(
            function () use ($pdfContent) {
                echo $pdfContent;
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="teacher-dashboard-report.pdf"',
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }
}
