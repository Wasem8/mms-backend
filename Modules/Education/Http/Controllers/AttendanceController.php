<?php

namespace Modules\Education\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\ApiResponse;
use Modules\Education\Services\AttendanceService;

class AttendanceController
{
    public function __construct(private AttendanceService $service) {}

    public function store(Request $request)
    {
        $attendance = $this->service->mark($request->all());

        return ApiResponse::success($attendance, 'Attendance saved.');
    }

    public function index(Request $request)
    {
        return ApiResponse::success(
            $this->service->list($request->all()),
            'Attendance list.'
        );
    }
}
