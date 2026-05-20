<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\ParentDashboardService;

class ParentDashboardController extends Controller
{
    protected $parentDashboardService;

    // حقن السيرفس المخصص لولي الأمر
    public function __construct(ParentDashboardService $parentDashboardService)
    {
        $this->parentDashboardService = $parentDashboardService;
    }

    /**
     * جلب إحصائيات وبيانات أبناء ولي الأمر الحالي
     */
    public function index(Request $request)
    {
        $parentId = auth()->id();

        $stats = $this->parentDashboardService->getParentDashboardStats($parentId);

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب بيانات لوحة تحكم ولي الأمر بنجاح',
            'data'    => $stats
        ]);
    }
}
