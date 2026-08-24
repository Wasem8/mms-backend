<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\MosqueDashboardService;
use Modules\Dashboard\Services\MosqueManagerReportService;

class MosqueManagerDashboardController extends Controller
{
    public function index(
        Request $request,
        MosqueDashboardService $mosqueDashboardService
    ) {
        $data = $mosqueDashboardService->getDashboardData(
            $request->user()
        );

        return response()->json([
            'status' => true,
            'message' => 'تم جلب بيانات لوحة التحكم بنجاح',
            'data' => $data
        ]);
    }

    public function statistics(
        Request $request,
        MosqueDashboardService $mosqueDashboardService
    ) {
        $data = $mosqueDashboardService->getMosqueStatistics(
            $request->user()
        );

        return response()->json([
            'status' => true,
            'message' => 'تم جلب إحصائيات المسجد بنجاح',
            'data' => $data,
        ]);
    }

    public function exportPdf(
        Request $request,
        MosqueManagerReportService $reportService
    ) {
        $result = $reportService->generate($request->user());

        return ApiResponse::success(
            $result,
            'تم إنشاء تقرير مدير المسجد بنجاح'
        );
    }
}
