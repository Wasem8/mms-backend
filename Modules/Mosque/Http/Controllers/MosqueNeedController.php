<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Mosque\Services\MosqueNeedsService;
use App\Support\ApiResponse;
use Modules\Mosque\Http\Requests\ListMosqueNeedsRequest;
use Modules\Mosque\Http\Requests\NearbyMosqueNeedsRequest;
use Modules\Mosque\Http\Requests\StoreMosqueNeedRequest;
use Modules\Mosque\Http\Requests\UpdateMosqueNeedRequest;

class MosqueNeedController extends Controller
{
    public function __construct(
        private MosqueNeedsService $service
    ) {}


    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $needs = $this->service->list($perPage);

        return ApiResponse::success(
            $needs->items(),
            __('messages.mosque.needs_retrieved'),
            ApiResponse::pagination($needs)
        );
    }

    public function AllNeeds(ListMosqueNeedsRequest $request)
    {
        $perPage = $request->get('per_page', 10);

        $filters = $request->toFilters();

        $needs = $this->service->listAggregate($filters, $perPage);

        return ApiResponse::success(
            $needs->items(),
            __('messages.mosque.all_needs_retrieved'),
            ApiResponse::pagination($needs)
        );
    }
    private function parseNear(?string $near): ?array
    {
        if (! $near) {
            return null;
        }

        $parts = explode(',', $near);

        if (count($parts) !== 2 || ! is_numeric($parts[0]) || ! is_numeric($parts[1])) {
            return null;
        }

        $lat = (float) $parts[0];
        $lng = (float) $parts[1];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [$lat, $lng];
    }
    public function show($mosqueId,$needId)
    {
        try {
            $need = $this->service->getNeedForMosque($mosqueId, $needId);
            return ApiResponse::success(
                $need,
                __('messages.mosque.need_retrieved')
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                $e->getMessage(),
                404
            );
        }
    }

    public function NeedById($needId){
        $need = $this->service->getNeedById($needId);

        if (!$need) {
            return ApiResponse::error(
                __('messages.mosque.need_not_found'),
                404
            );
        }
        return ApiResponse::success(
            $need,
            __('messages.mosque.need_retrieved')
        );
    }


    public function store(StoreMosqueNeedRequest $request)
    {
        $data = $request->validated();


        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $need = $this->service->create($data);

        return ApiResponse::success(
            $need,
            __('messages.mosque.need_created')
        );
    }


    public function update(UpdateMosqueNeedRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $need = $this->service->update($id, $data);

            return ApiResponse::success(
                $need,
                __('messages.mosque.need_updated')
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                $e->getMessage(),
                404
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);

            return ApiResponse::success(
                null,
                __('messages.mosque.need_deleted')
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                $e->getMessage(),
                404
            );
        }
    }
    public function nearbyWithNeeds(NearbyMosqueNeedsRequest $request)
    {
        $data = $request->validated();

        $needs = $this->service->listNearbyMosquesWithNeeds(
            (float) $data['lat'],
            (float) $data['lng'],
            isset($data['radius_km']) ? (float) $data['radius_km'] : null,
            $data['urgent_only'] ?? false,
            (int) ($data['per_page'] ?? 10)
        );

        return ApiResponse::success(
            $needs->items(),
            __('messages.mosque.nearby_needs_retrieved'),
            ApiResponse::pagination($needs)
        );
    }

}
