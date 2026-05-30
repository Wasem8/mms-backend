<?php

namespace Modules\MaintenanceRequest\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\MaintenanceRequest\Http\Requests\CreateMaintenanceRequest;
use Modules\MaintenanceRequest\Http\Requests\ProcessMaintenanceRequest;
use Modules\MaintenanceRequest\Http\Requests\UpdateMaintenanceRequest;
use Modules\MaintenanceRequest\Models\Maintenance;
use Symfony\Component\HttpFoundation\Request;
use Modules\Maintenancerequest\Services\MaintenanceService;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        private readonly MaintenanceService $service,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'priority', 'per_page']);
        $mosque = $request->user()->mosque;

        if (!$mosque) {
            return ApiResponse::success([], 'No mosque assigned to this user.');
        }

        $filters['mosque_id'] = $mosque->id;
        $paginator = $this->service->getList($filters);

        return ApiResponse::success(
            $paginator->items(),
            'Maintenance requests retrieved successfully.',
            ApiResponse::pagination($paginator)
        );
    }

    // POST /maintenance
    public function store(CreateMaintenanceRequest $request)
    {
        $maintenance = $this->service->submitRequest(
            data: $request->except('files'),
            files: $request->file('files', []),
        );

        return ApiResponse::success($maintenance, 'Maintenance request submitted successfully.', 201);
    }

    // GET /maintenance/{id}
    public function show(string $id, Request $request)
    {
        $maintenance = $this->service->getDetails((int) $id, [
            'mosque_id' => $request->user()->mosque_id,
        ]);

        return ApiResponse::success($maintenance, 'Maintenance request retrieved successfully.');
    }

    // PUT /maintenance/{id}
    public function update(UpdateMaintenanceRequest $request, int $id)
    {
        $maintenance = $this->service->update($id, $request->validated());

        return ApiResponse::success($maintenance, 'Maintenance request updated successfully.');
    }

    // DELETE /maintenance/{id}
    public function destroy(int $id)
    {
        $this->service->delete($id);

        return ApiResponse::success(null, 'Maintenance request deleted successfully.');
    }

    // GET /maintenance/track/{maintenance_number}
    public function track(string $maintenance_number)
    {
        $maintenance = $this->service->trackRequest($maintenance_number);

        if (! $maintenance) {
            return ApiResponse::error('Maintenance request not found.', 404);
        }

        return ApiResponse::success($maintenance, 'Maintenance request retrieved successfully.');
    }

    // =========================================================================
    //  Admin (Region Manager)
    // =========================================================================

    // GET /maintenance/admin
    public function adminIndex(Request $request)
    {
        $filters = $request->only(['status', 'category', 'priority', 'per_page']);

        $data = $this->service->getForAdmin($filters);

        return ApiResponse::success($data, 'All maintenance requests retrieved successfully.');
    }

    // PUT /maintenance/{id}/process
    public function process(ProcessMaintenanceRequest $request, int $id)
    {
        $validated = $request->validated();

        $maintenance = $this->service->updateStatus(
            id: $id,
            newStatus: $validated['status'],
            changedBy: $request->user()->name,
            note: $validated['notes'] ?? null,
        );

        return ApiResponse::success($maintenance, 'Maintenance request processed successfully.');
    }

    public function statistics(Request $request)
    {
        $filters = $request->only(['mosque_id']);

        $stats = $this->service->getStatistics($filters);

        return ApiResponse::success($stats, 'Statistics retrieved successfully.');
    }
}
