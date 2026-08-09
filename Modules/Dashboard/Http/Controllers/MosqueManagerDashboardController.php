<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\MosqueDashboardService;

class MosqueManagerDashboardController extends Controller
{
    // الحقن المباشر للـ Service عبر الـ Constructor أو عبر الدالة
    public function index(Request $request, MosqueDashboardService $mosqueDashboardService)
    {
        $data = $mosqueDashboardService->getDashboardData($request->user());

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب بيانات لوحة التحكم بنجاح',
            'data'    => $data
        ]);
    }
}
