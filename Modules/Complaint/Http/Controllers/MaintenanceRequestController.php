<?php

namespace Modules\Complaint\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Complaint\ApiResource\MaintenanceRequestResource;
use Modules\Complaint\Http\Requests\CreateMaintenanceRequestRequest;
use Modules\Complaint\Service\MaintenanceRequestService;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        private readonly MaintenanceRequestService $service,
    ) {}

    public function index(Request $request)
    {
        $authUser = auth()->user();

        abort_if(
            ! $authUser?->isMosqueManager() || ! $authUser->mosque_id,
            403,
            'Only mosque managers may list maintenance requests.',
        );

        $paginator = $this->service->listForMosque(
            mosqueId: $authUser->mosque_id,
            status: $request->query('status'),
            perPage: (int) $request->query('per_page', 15),
        );

        return ApiResponse::success(
            data: MaintenanceRequestResource::collection($paginator),
            message: 'Maintenance requests retrieved successfully.',
            pagination: $paginator,
        );
    }

    public function store(CreateMaintenanceRequestRequest $request)
    {
        $result = $this->service->create(
            data: $request->validated(),
            files: $request->file('attachments', []),
        );

        return response()->json([
            'status'  => true,
            'message' => 'Maintenance request submitted successfully.',
            'data'    => $result,
        ]);
    }

    public function track(string $reference)
    {
        $maintenanceRequest = $this->service->findByReference($reference);

        return ApiResponse::success(
            data: [
                'reference_number'      => $maintenanceRequest->reference_number,
                'title'                 => $maintenanceRequest->title,
                'current_status'        => $maintenanceRequest->status,
                'admin_resolution_note' => $maintenanceRequest->rejection_reason,
                'created_at'            => $maintenanceRequest->created_at->toIso8601String(),
                'status_history'        => $this->service->buildStatusHistory($maintenanceRequest),
            ],
            message: 'Maintenance request status retrieved successfully.',
        );
    }
}
