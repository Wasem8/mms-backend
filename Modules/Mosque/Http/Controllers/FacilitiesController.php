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
            __('messages.mosque.facilities_retrieved')
        );
    }

    public function store(StoreFacilityRequest $request)
    {
        $facility = $this->facilityService->createFacility(
            $request->validated()
        );

        return ApiResponse::success(
            $facility,
            __('messages.mosque.facility_created')
        );
    }

    // PUT /facilities/{facility}
    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $facility = $this->facilityService->updateFacility($facility, $request->validated());

        return ApiResponse::success(
            $facility,
            __('messages.mosque.facility_updated')
        );
    }

    public function destroy(Facility $facility)
    {
        $this->facilityService->deleteFacility($facility);

        return ApiResponse::success([], __('messages.mosque.facility_deleted'));
    }

    public function byMosque(int $mosqueId)
    {
        return ApiResponse::success(
            $this->facilityService->getFacilitiesByMosque($mosqueId),
            __('messages.mosque.mosque_facilities_retrieved')
        );
    }

    public function sync(Request $request, Mosque $mosque)
    {
        $data = $request->validate([
            'facility_ids' => ['required', 'array'],
            'facility_ids.*' => ['exists:facilities,id'],
        ]);

        $this->facilityService->syncMosqueFacilities($mosque, $data['facility_ids']);

        return ApiResponse::success(null, __('messages.mosque.facilities_synced'));
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
            __('messages.mosque.facilities_attached')
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
            __('messages.mosque.facilities_detached')
        );
    }
}
