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

    public function exportPdf(Request $request)
    {
        try {
            $mosqueId = auth()->user()->mosque_id;
            $filters = $request->only(['halaqa_id']);

            $stats = $this->service->getSupervisorStatsForPdf($mosqueId, $filters);

            $viewPath = base_path('Modules/Dashboard/resources/views/Pdf/supervisor_report.blade.php');

            $html = view()->file($viewPath, [
                'stats' => $stats,
                'user' => auth()->user(),
            ])->render();

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'supervisor-dashboard-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';

            // استخدام stream() بدلاً من download()
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'public, max-age=0',
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            return ApiResponse::error('خطأ في تصدير التقرير: ' . $e->getMessage(), 500);
        }
    }

}
