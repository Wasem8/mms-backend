<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\AdminDashboardService;

class AdminDashboardController extends Controller
{
    public function index(AdminDashboardService $service)
    {
        return ApiResponse::success(
            $service->getDashboardData(),
            'تم جلب إحصائيات لوحة تحكم المدير بنجاح'
        );
    }

    public function exportPdf(
        Request $request,
        AdminDashboardService $service
    ) {
        $result = $service->generatePdfResponse($request->user());

        return ApiResponse::success(
            $result,
            'تم إنشاء تقرير المدير بنجاح'
        );
    }

    public function superAdminDashboard(Request $request, AdminDashboardService $service)
    {
        $filters = $request->only(['module', 'date_from', 'date_to', 'per_page', 'page']);

        $data = $service->getSuperAdminDashboard($request->user(), $filters);

        return ApiResponse::success(
            $data,
            'تم جلب بيانات لوحة تحكم مدير المنطقة بنجاح'
        );
    }
}
