<?php

namespace Modules\Complaint\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Complaint\ApiResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Complaint\ApiResource\MaintenanceRequestResource;
use Modules\Complaint\Http\Requests\CreateMaintenanceRequestRequest;
use Modules\Complaint\Service\MaintenanceRequestService;
use Modules\Mosque\Models\Mosque;

class MaintenanceRequestController extends Controller
{

    public function __construct(
        private readonly MaintenanceRequestService $service,
    ) {}

    public function index(Request $request)
    {
        $authUser = auth()->user();

        if (! $authUser || ! $authUser->isMosqueManager() || ! $authUser->mosque_id) {
            abort(403, 'Unauthorized access to maintenance requests.');
        }

        $mosqueId = $authUser->mosque_id;

        $paginator = $this->service->listForMosque(
            mosqueId: $mosqueId,
            status: $request->query('status'),
            perPage: (int) $request->query('per_page', 15),
        );

        $data = MaintenanceRequestResource::collection($paginator);


        return ApiResponse::success(
            data: $data,
            message: 'Maintenance requests retrieved successfully.',
            pagination: $paginator,
        );
    }

    public function store(CreateMaintenanceRequestRequest $request)
    {
        $maintenanceRequest = $this->service->create($request->toDTO());

        $resource = new MaintenanceRequestResource(
            $maintenanceRequest->load('mosque')
        );

        return ApiResponse::success(
            data: $resource,
            message: 'Maintenance request created successfully.',
        );
    }

    public function track(string $reference)
    {
        $authUser = auth()->user();
        if (! $authUser || (! $authUser->isMosqueManager() && ! $authUser->hasRole('super_admin'))) {
            abort(403, 'Unauthorized access to maintenance request tracking.');
        }

        $maintenanceRequest = $this->service->findByReference($reference);
        if ($authUser->isMosqueManager() && $maintenanceRequest->mosque_id !== $authUser->mosque_id) {
            abort(404);
        }

        // Build status history: initial creation entry + change logs
        $initial = [
            'status' => $maintenanceRequest->status ?? 'pending',
            'date' => $maintenanceRequest->created_at,
            'note' => 'Request created',
        ];

        $logs = $maintenanceRequest->statusLogs->map(function ($log) {
            return [
                'status' => $log->new_status,
                'date' => $log->changed_at,
                'note' => $log->note,
            ];
        });

        $fullHistory = collect([$initial])->concat($logs);

        return ApiResponse::success([
            'reference_number' => $maintenanceRequest->reference_number,
            'title' => $maintenanceRequest->title,
            'current_status' => $maintenanceRequest->status,
            'admin_resolution_note' => $maintenanceRequest->rejection_reason,
            'created_at' => $maintenanceRequest->created_at,
            'status_history' => $fullHistory,
        ], 'Maintenance request status retrieved successfully.');
    }
}
