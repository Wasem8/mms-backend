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
        // قراءة halaqa_id الاختياري من الطلب
        $halaqaId = $request->query('halaqa_id');

        $stats = $this->service->getDashboard($mosqueId, $halaqaId);

        return ApiResponse::success($stats, 'تم جلب إحصائيات لوحة التحكم بنجاح');
    }

    public function formatted(Request $request)
    {
        $mosqueId = auth()->user()->mosque_id;
        $halaqaId = $request->query('halaqa_id');

        $data = $this->service->getFormattedDashboard($mosqueId, $halaqaId);

        return response()->json($data);
    }

    /**
     * توليد تقرير الـ PDF للمشرف وإرجاع رابط سحابي موقّع ومكّش
     */
    public function exportPdf(Request $request)
    {
        $mosqueId = auth()->user()->mosque_id;

            // جلب رابط التقرير الموقّع من السيرفيس باستخدام mPDF
            $result = $this->service->generateSupervisorPdfResponse(
                $mosqueId
            );

            return ApiResponse::success(
                $result,
                'تم إنشاء التقرير بنجاح'
            );
    }
}
