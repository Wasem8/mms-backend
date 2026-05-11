<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Support\ApiResponse;
use Modules\Dashboard\Services\DashboardService;

class SupervisorDashboardController
{
    public function __construct(private DashboardService $service) {}

    public function index()
    {
        $mosqueId = auth()->user()->mosque_id;
        $stats = $this->service->getSupervisorStats($mosqueId);

        return ApiResponse::success($stats, 'تم جلب إحصائيات لوحة التحكم بنجاح');
    }
}
