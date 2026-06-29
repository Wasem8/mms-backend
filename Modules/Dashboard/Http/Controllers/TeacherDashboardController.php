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

    public function bootstrap(Request $request)
    {
        $teacherId = auth()->id();

        $data = $this->dashboardService->getTeacherBootstrap($teacherId);

        return ApiResponse::success(
            $data,
            'تم تحميل بيانات التهيئة بنجاح'
        );
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

    public function exportPdf()
    {
        $teacherId = auth()->id();

        // السيرفس تعود الآن بمصفوفة تحتوي على الـ URL والـ Cached status
        $report = $this->dashboardService
            ->generateTeacherReportPdf($teacherId);

        return ApiResponse::success(
            $report,
            'تم إنشاء تقرير المعلم بنجاح'
        );
    }
}
