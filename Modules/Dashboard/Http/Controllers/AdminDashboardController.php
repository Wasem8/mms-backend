<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminActivityService;
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

    /**
     * سجل عمليات المساجد المشتق من بيانات الموديولات (دون جدول جديد).
     * للسوبر أدمن: كل عمليات المساجد. لمدير المسجد: مُقيّد بمسجده.
     */
    public function mosqueOperations(Request $request, SuperAdminActivityService $service)
    {
        $filters = $request->only(['module', 'date_from', 'date_to', 'per_page', 'page']);

        $result = $service->getMosqueOperations($request->user(), $filters);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $result->items(),
            'meta'    => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
                'from'         => $result->firstItem(),
                'to'           => $result->lastItem(),
            ],
            'links'   => [
                'first' => $result->url(1),
                'last'  => $result->url($result->lastPage()),
                'prev'  => $result->previousPageUrl(),
                'next'  => $result->nextPageUrl(),
            ],
        ]);
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
