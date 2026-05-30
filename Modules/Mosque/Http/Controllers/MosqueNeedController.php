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
            'Needs retrieved successfully',
            ApiResponse::pagination($needs)
        );
    }

    public function AllNeeds(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $filters = [
            'status' => $request->get('status'),
            'type'   => $request->get('type'),
            'urgent' => $request->get('urgent'),
        ];

        $needs = $this->service->listAggregate($filters, $perPage);

        return ApiResponse::success(
            $needs->items(),
            'Needs across all mosques retrieved successfully',
            ApiResponse::pagination($needs)
        );
    }

    public function show($mosqueId,$needId)
    {
        try {
            $need = $this->service->getNeedForMosque($mosqueId, $needId);
            return ApiResponse::success(
                $need,
                'Need retrieved successfully'
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
                'Need not found',
                404
            );
        }
        return ApiResponse::success(
            $need,
            'Need retrieved successfully'
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
            'Need created successfully'
        );
    }


    public function update(UpdateMosqueNeedRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $need = $this->service->update($id, $data);

            return ApiResponse::success(
                $need,
                'Need updated successfully'
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
                'Need deleted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                $e->getMessage(),
                404
            );
        }
    }

}
