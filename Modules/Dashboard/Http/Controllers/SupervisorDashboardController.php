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
        $mosqueId = auth()->user()->mosque_id;
        $filters = $request->only(['halaqa_id']);

        // جلب محتوى الـ PDF كـ Binary String من السيرفس
        $pdfContent = $this->service->generateSupervisorReportPdf(
            $mosqueId,
            $filters
        );

        // 🎯 الحل: استخدام الـ Stream Response لفرض نوع المحتوى بشكل صارم على المتصفح والسواجر
        return response()->stream(
            function () use ($pdfContent) {
                echo $pdfContent;
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="supervisor-report.pdf"',
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

}
