<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Dashboard\Services\SupervisorDashboardService; // 🎯 تعديل مسار السيرفيس الصحيح

class SupervisorDashboardController
{
    // 🎯 حقن السيرفيس الصحيح للمشرف هنا
    public function __construct(private SupervisorDashboardService $service) {}

    /**
     * جلب إحصائيات لوحة التحكم الرئيسية للمشرف العام (الـ الويب / التطبيق)
     */
    public function index(Request $request)
    {
        $mosqueId = auth()->user()->mosque_id;

        // 🎯 استدعاء الدالة الصحيحة من السيرفيس getDashboard
        $stats = $this->service->getDashboard($mosqueId);

        return ApiResponse::success($stats, 'تم جلب إحصائيات لوحة التحكم بنجاح');
    }

    /**
     * توليد تقرير الـ PDF للمشرف وإرجاع رابط سحابي موقّع ومكّش
     */
    public function exportPdf(Request $request)
    {
        $mosqueId = auth()->user()->mosque_id;
        $filters = $request->only(['halaqa_id']);

        try {
            // جلب رابط التقرير الموقّع من السيرفيس باستخدام mPDF
            $result = $this->service->generateSupervisorPdfResponse(
                $mosqueId,
                $filters
            );

            // إرجاع الرابط كـ JSON للاستخدام الفوري في الفرونت إند
            return response()->json([
                'success' => true,
                'url'     => $result['url'],
                'cached'  => $result['cached']
            ]);

        } catch (\Throwable $e) {
            Log::error('Supervisor Export PDF Controller Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء توليد التقرير، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
}
