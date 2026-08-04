<?php

namespace Modules\Mosque\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Mosque\Services\MosqueNeedsService;
use App\Support\ApiResponse;
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

    public function AllNeeds(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $allowedSortFields = ['urgency', 'funding_gap', 'created_at', 'deadline', 'distance'];
        $sortBy = in_array($request->get('sort_by'), $allowedSortFields)
            ? $request->get('sort_by')
            : 'created_at';

        $sortOrder = strtolower($request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $near = $this->parseNear($request->get('near'));

        $filters = [
            'status'     => $request->get('status'),
            'type'       => $request->get('type'),
            'urgent'     => $request->get('urgent'),
            'city'       => $request->get('city'),
            'mosque_id'  => $request->get('mosque_id'),
            'search'     => $request->get('search'),
            'sort_by'    => $near && $sortBy === 'created_at' ? 'distance' : $sortBy,
            'sort_order' => $sortOrder,
            'near'       => $near,
            'radius_km'  => $request->get('radius_km'),
        ];

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

}
