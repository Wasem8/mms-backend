<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Mosque\Http\Requests\StoreFacilityRequest;
use Modules\Mosque\Http\Requests\UpdateFacilityRequest;
use Modules\Mosque\Models\Facility;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Services\FacilityService;

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

    public function store(StoreFacilityRequest $request)
    {
        $facility = $this->facilityService->createFacility(
            $request->validated()
        );

        return ApiResponse::success(
            $facility,
            'Facility created and attached to the mosque successfully.'
        );
    }

    // PUT /facilities/{facility}
    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $facility = $this->facilityService->updateFacility($facility, $request->validated());

        return ApiResponse::success(
            $facility,
            'Facility updated successfully.'
        );
    }

    public function destroy(Facility $facility)
    {
        $this->facilityService->deleteFacility($facility);

        return ApiResponse::success([], 'Facility deleted successfully.');
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
            'facility_ids'   => ['required', 'array'],
            'facility_ids.*' => ['exists:facilities,id'],      
        ]);

        $this->facilityService->attachFacilitiesToMosque($mosque, $data['facility_ids']);

        return ApiResponse::success(
            null,
            'تم ربط المرافق بالمسجد بنجاح.'
        );
    }
    public function detach(Request $request, Mosque $mosque)
    {
        $data = $request->validate([
            'facility_ids'   => ['required', 'array'],
            'facility_ids.*' => ['exists:facilities,id'], // التأكد أن المرافق موجودة في النظام
        ]);

        $this->facilityService->detachFacilitiesFromMosque($mosque, $data['facility_ids']);

        return ApiResponse::success(
            null,
            'تم فك ارتباط المرافق بالمسجد بنجاح.'
        );
    }

}
