<?php

namespace Modules\Education\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\ApiResponse;
use Modules\Education\Http\Requests\StoreAttendanceRequest;
use Modules\Education\Services\AttendanceService;
use Modules\Education\Transformers\AttendanceResource;

class AttendanceController
{
    public function __construct(private AttendanceService $service) {}

    public function index(Request $request)
    {
        $attendances = $this->service->index($request->all());

        return ApiResponse::success(
            AttendanceResource::collection($attendances->items()),
            'تم جلب سجل الحضور بنجاح.',
            $attendances
        );
    }

    public function storeBulk(StoreAttendanceRequest $request)
    {
        $this->service->storeBulk($request->validated());

        return ApiResponse::success([], 'تم تسجيل الحضور بنجاح');
    }


}
