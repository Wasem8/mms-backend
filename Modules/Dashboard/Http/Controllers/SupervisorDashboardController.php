<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Support\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Dashboard\Services\DashboardService;

class SupervisorDashboardController
{
    public function __construct(private DashboardService $service) {}

    public function index(Request $request)
    {
        $mosqueId = auth()->user()->mosque_id;

        $filters = $request->only(['halaqa_id']);

        $stats = $this->service->getSupervisorStats($mosqueId, $filters);

        return ApiResponse::success($stats, 'تم جلب إحصائيات لوحة التحكم بنجاح');
    }

    public function exportPdf(Request $request, DashboardService $service)
    {
        $mosqueId = auth()->user()->mosque_id;

        $filters = [
            'halaqa_id' => $request->halaqa_id,
        ];

        $data = $service->getSupervisorStatsForPdf($mosqueId, $filters);

        $pdf = Pdf::loadView(
            'dashboard::supervisor_report',
            $data
        );

        return $pdf->download('supervisor-report.pdf');
    }

}
