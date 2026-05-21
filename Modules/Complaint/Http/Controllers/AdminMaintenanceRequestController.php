<?php

namespace Modules\Complaint\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Complaint\ApiResource\MaintenanceRequestResource;
use Modules\Complaint\Http\Requests\ProcessMaintenanceRequestRequest;
use Modules\Complaint\Service\MaintenanceRequestService;

class AdminMaintenanceRequestController extends Controller
{
    public function __construct(
        private readonly MaintenanceRequestService $service,
    ) {}

    public function index(Request $request)
    {
        $urgency = $request->query('is_urgent');
        // ensure only allowed values are forwarded
        $allowed = ['low', 'medium', 'high', 'urgent'];
        if (! in_array($urgency, $allowed, true)) {
            $urgency = null;
        }

        $paginator = $this->service->listForAdmin(
            status: $request->query('status'),
            category: $request->query('category'),
            urgency: $urgency,
            perPage: (int) $request->query('per_page', 15),
        );

        $data = MaintenanceRequestResource::collection($paginator);

        return ApiResponse::success(
            data: $data,
            message: 'All maintenance requests retrieved successfully.',
            pagination: $paginator,
        );
    }

    public function process(ProcessMaintenanceRequestRequest $request, int $id,)
    {
        $maintenanceRequest = $this->service->findOrFail($id);

        $updated = $this->service->process(
            maintenanceRequest: $maintenanceRequest,
            dto: $request->toDTO(),
        );

        $resource = new MaintenanceRequestResource(
            $updated->load('mosque', 'regionManager')
        );

        return ApiResponse::success(
            data: $resource,
            message: 'Maintenance request processed successfully.',
        );
    }
}
