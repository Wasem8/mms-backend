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
use Modules\MaintenanceRequest\Http\Resources\PublicMaintenanceResource;

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
            abort(403, __('messages.maintenance.no_mosque_assigned'));
        }

        return $mosque->id;
    }

    // =========================================================================
    //  MOSQUE MANAGER ENDPOINTS
    // =========================================================================

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q'           => ['required', 'string', 'min:1'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $filters = ['search' => $validated['q']];

        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        }

        $filters['per_page'] = (int) ($validated['per_page'] ?? 15);
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
        ], __('messages.maintenance.search_retrieved'));
    }

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
        ], __('messages.maintenance.all_retrieved'));

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

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), __('messages.maintenance.created'), 201);
    }

    public function show(Request $request, int $id)
    {
        $filters = [];
        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        }
        $maintenance = $this->service->getDetails($id, $filters);

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), __('messages.maintenance.retrieved'));
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

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), __('messages.maintenance.updated'));
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

        return ApiResponse::success(null, __('messages.maintenance.deleted'));
    }

    public function track(string $maintenanceNumber)
    {
        $maintenance = $this->service->trackRequest($maintenanceNumber);

        if (!$maintenance) {
            return ApiResponse::error(__('messages.maintenance.not_found'), 404);
        }

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), __('messages.maintenance.retrieved'));
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
            'message' => __('messages.maintenance.success'),
            'data'    => $data,
        ]);
    }

    public function recentRequests()
    {
        $limit = (int) request()->query('limit', 5);
        $data  = $this->service->getRecentRequests($this->getManagerMosqueId(), $limit);

        return response()->json([
            'status'  => true,
            'message' => __('messages.maintenance.success'),
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

        return ApiResponse::success($maintenance->loadMissing(['files', 'statusLogs', 'mosque']), __('messages.maintenance.processed'));
    }

    public function publicIndex(Request $request)
    {
        $filters = $request->only(['status', 'category', 'priority', 'mosque_id', 'per_page']);
        $paginator = $this->service->getPublicList($filters);

        return ApiResponse::success(
            PublicMaintenanceResource::collection($paginator->items())->resolve(),
            __('messages.maintenance.public_retrieved'),
            ApiResponse::pagination($paginator)
        );
    }

    public function publicShow(int $id)
    {
        $maintenance = $this->service->getPublicDetails($id);

        return ApiResponse::success(
            new PublicMaintenanceResource($maintenance),
            __('messages.maintenance.retrieved')
        );
    }
}
