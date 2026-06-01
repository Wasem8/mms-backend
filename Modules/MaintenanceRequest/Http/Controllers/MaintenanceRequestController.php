<?php

namespace Modules\MaintenanceRequest\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\MaintenanceRequest\Http\Requests\CreateMaintenanceRequest;
use Modules\MaintenanceRequest\Service\MaintenanceService;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        private readonly MaintenanceService $service,
    ) {}

    public function store(CreateMaintenanceRequest $request)
    {
        $maintenance = $this->service->submitRequest(
            data: $request->except('files'),
            files: $request->file('files', []),
        );

        return ApiResponse::success($maintenance, 'Maintenance request submitted successfully.', 201);
    }


    

}
