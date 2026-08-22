<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\MosqueReportService;

class MosqueReportController extends Controller
{
    public function census(Request $request, MosqueReportService $service)
    {
        $result = $service->regionalCensus($request->user());

        return ApiResponse::success($result, 'تم إنشاء التقرير الإحصائي الشامل للمساجد بنجاح');
    }

    public function factSheet(Request $request, MosqueReportService $service)
    {
        $mosqueId = $request->query('mosque_id') ? (int) $request->query('mosque_id') : null;

        $result = $service->mosqueFactSheet($request->user(), $mosqueId);

        return ApiResponse::success($result, 'تم إنشاء بطاقة المسجد التعريفية بنجاح');
    }

    public function readiness(Request $request, MosqueReportService $service)
    {
        $result = $service->seasonsReadiness($request->user());

        return ApiResponse::success($result, 'تم إنشاء تقرير الجاهزية للمواسم بنجاح');
    }
}
