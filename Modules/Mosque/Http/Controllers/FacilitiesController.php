<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Mosque\Models\Mosque;
use Modules\Facility\Services\FacilityService;

class FacilitiesController extends Controller
{
    public function __construct(
        private readonly FacilityService $facilityService
    ) {}

    public function index()
    {
        return ApiResponse::success(
            $this->facilityService->getAllFacilities(),
            'Facilities retrieved successfully'
        );
    }

    public function byMosque(int $mosqueId)
    {
        return ApiResponse::success(
            $this->facilityService->getFacilitiesByMosque($mosqueId),
            'Mosque facilities retrieved successfully'
        );
    }

    public function sync(Request $request, Mosque $mosque)
    {
        $data = $request->validate([
            'facility_ids' => ['required', 'array'],
            'facility_ids.*' => ['exists:facilities,id'],
        ]);

        $this->facilityService->syncMosqueFacilities($mosque, $data['facility_ids']);

        return ApiResponse::success(null, 'Facilities synced successfully');
    }

    public function attach(Request $request, Mosque $mosque)
    {
        $data = $request->validate([
            'facility_ids' => ['required', 'array'],
            'facility_ids.*' => ['exists:facilities,id'],
        ]);

        $this->facilityService->addFacilitiesToMosque($mosque, $data['facility_ids']);

        return ApiResponse::success(null, 'Facilities attached successfully');
    }

    public function detach(Request $request, Mosque $mosque)
    {
        $data = $request->validate([
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['exists:facilities,id'],
        ]);

        $this->facilityService->removeFacilitiesFromMosque(
            $mosque,
            $data['facility_ids'] ?? null
        );

        return ApiResponse::success(null, 'Facilities detached successfully');
    }
}
