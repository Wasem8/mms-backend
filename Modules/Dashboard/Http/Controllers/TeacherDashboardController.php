<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\TeacherDashboardService;

class TeacherDashboardController extends Controller
{
    protected $dashboardService;

    // حقن السيرفس داخل الكونترولر تلقائياً
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
}
