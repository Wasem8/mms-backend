<?php

namespace Modules\MaintenanceRequest\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\MaintenanceRequest\Http\Requests\CreateMaintenanceRequest;
use Modules\MaintenanceRequest\Service\MaintenanceService;
use Modules\MaintenanceRequest\Http\Requests\StoreMaintenanceRequest;
use Modules\MaintenanceRequest\Http\Requests\UpdateMaintenanceRequest;
use Modules\MaintenanceRequest\Http\Requests\ProcessMaintenanceRequest;
use Modules\Mosque\Models\Mosque;
use App\Support\ApiResponse;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        protected MaintenanceService $service
    ) {}

    private function getManagerMosqueId(): ?int
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('mosque_manager')) {
            return null;
        }

        $mosque = Mosque::where('manager_id', $user->id)->first();

        if (!$mosque) {
            abort(403, 'No mosque is assigned to your account.');
        }

        return $mosque->id;
    }

    // =========================================================================
    //  MOSQUE MANAGER ENDPOINTS
    // =========================================================================

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'priority', 'per_page']);

        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        }

        $paginator = $this->service->getList($filters);

        return ApiResponse::success([
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ]
        ], 'All maintenance requests retrieved successfully.');

    }

    /**
     * POST /maintenance
     * @param CreateMaintenanceRequest $request
     */
    public function store(CreateMaintenanceRequest $request)
    {
        $validated = $request->validated();
        $files = $request->file('files') ?? [];

        $maintenance = $this->service->submitRequest($validated, $files);

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), 'Maintenance request submitted successfully.', 201);
    }

    public function show(Request $request, int $id)
    {
        $filters = [];
        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        }
        $maintenance = $this->service->getDetails($id, $filters);

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), 'Maintenance request retrieved successfully.');
    }

    /**
     * PUT /maintenance/{id}
     * @param UpdateMaintenanceRequest $request
     */
    public function update(UpdateMaintenanceRequest $request, int $id)
    {
        $filters = [];
        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        }

        // Ensure ownership before updating
        $this->service->getDetails($id, $filters);

        // Update with validated data
        $this->service->update($id, $request->validated());

        // Refetch updated data
        $maintenance = $this->service->getDetails($id, $filters);

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), 'Maintenance request updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $filters = [];
        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        }
        $this->service->getDetails($id, $filters);

        $this->service->delete($id);

        return ApiResponse::success(null, 'Maintenance request deleted successfully.');
    }

    public function track(string $maintenanceNumber)
    {
        $maintenance = $this->service->trackRequest($maintenanceNumber);

        if (!$maintenance) {
            return ApiResponse::error('Resource not found.', 404);
        }

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), 'Maintenance request retrieved successfully.');
    }

    // =========================================================================
    //  SUPER ADMIN ENDPOINTS
    // =========================================================================

    public function adminIndex(Request $request)
    {
        $filters = $request->only(['status', 'category', 'priority', 'per_page']);
        $paginator = $this->service->getForAdmin($filters);

        return ApiResponse::success([
            'data'    => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function pageStats()
    {
        $mosqueId = $this->getManagerMosqueId();
        $data = $mosqueId
            ? $this->service->getPageStats($mosqueId)
            : $this->service->getPageStats(null);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $data,
        ]);
    }

    public function recentRequests()
    {
        $limit = (int) request()->query('limit', 5);
        $data  = $this->service->getRecentRequests($this->getManagerMosqueId(), $limit);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $data,
        ]);
    }

    /**
     * PUT /maintenance/admin/{id}
     * @param ProcessMaintenanceRequest $request
     */
    public function process(ProcessMaintenanceRequest $request, int $id)
    {
        $validated = $request->validated();

        $maintenance = $this->service->updateStatus(
            $id,
            $validated['status'],
            $request->user()->name,
            $validated['notes'] ?? null
        );

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), 'Maintenance request processed successfully.');
    }
}
