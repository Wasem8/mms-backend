<?php

namespace Modules\Donation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Donation\Services\CampaignService;
use Modules\Donation\Http\Requests\StoreCampaignRequest;
use Modules\Donation\Http\Requests\UpdateCampaignRequest;
use Modules\Donation\Services\CampaignAnalyticsService;
use Illuminate\Support\Collection;
use Modules\Donation\ApiResource\CampaignResource;
use Modules\Mosque\Models\Mosque;

class CampaignController extends Controller
{

    protected $campaignService;
    protected $analyticsService;

    public function __construct(CampaignService $campaignService, CampaignAnalyticsService $analyticsService)
    {
        $this->campaignService = $campaignService;
        $this->analyticsService = $analyticsService;
    }

    protected function getManagerMosqueId(): ?int
    {
        $user = auth()->guard('api')->user();

        if (!$user || !$user->hasRole('mosque_manager')) {
            return null;
        }

        $mosque = Mosque::where('manager_id', $user->id)->first();

        if (!$mosque) {
            abort(403, 'This account is not linked to a mosque.');
        }

        return $mosque->id;
    }

    public function index()
    {
        $filters = request()->only(['search', 'status', 'priority', 'sort_by', 'sort_order', 'per_page', 'mosque_id']);
        $campaigns = $this->campaignService->getFilteredCampaigns($filters);

        return ApiResponse::success(
            CampaignResource::collection($campaigns)->resolve(),
            'Campaigns retrieved successfully',
            $campaigns
        );
    }

    public function mosqueIndex()
    {
        $mosqueId = $this->getManagerMosqueId();

        $filters = request()->only(['search', 'status', 'priority', 'sort_by', 'sort_order', 'per_page']);
        $filters['mosque_id'] = $mosqueId;

        $campaigns = $this->campaignService->getFilteredCampaigns($filters);

        return ApiResponse::success(
            CampaignResource::collection($campaigns)->resolve(),
            'Campaigns retrieved successfully',
            $campaigns
        );
    }

    public function showByMosque($mosqueId)
    {
        $filters = request()->only(['search', 'status', 'priority', 'sort_by', 'sort_order', 'per_page']);
        $filters['mosque_id'] = $mosqueId;
        $campaigns = $this->campaignService->getFilteredCampaigns($filters);

        return ApiResponse::success(
            CampaignResource::collection($campaigns)->resolve(),
            'Campaigns retrieved successfully',
            $campaigns
        );
    }

    public function show($id)
    {
        $campaign = $this->campaignService->getCampaignById($id);
        return ApiResponse::success(new CampaignResource($campaign), 'Campaign retrieved successfully');
    }


    public function store(StoreCampaignRequest $request)
    {
        $data = $request->validated();

        $data['mosque_id'] = $this->getManagerMosqueId();

        $campaign = $this->campaignService->createCampaign($data);
        return ApiResponse::success(new CampaignResource($campaign), 'Campaign created successfully', 201);
    }

    public function update(UpdateCampaignRequest $request, $id)
    {
        $campaign = $this->campaignService->updateCampaign($id, $request->all());
        return ApiResponse::success(new CampaignResource($campaign), 'Campaign updated successfully');
    }

    public function stats(int $mosqueId)
    {
        $data = $this->campaignService->getStatsByMosque($mosqueId);
        return ApiResponse::success($data, 'Success');
    }

    public function statsForAll()
    {
        $data = $this->campaignService->getStatsForAll();
        return ApiResponse::success($data, 'Success');
    }

    public function analytics(int $id)
    {
        $campaign  = $this->campaignService->getCampaignById($id);
        $analytics = $this->analyticsService->getAnalytics($campaign);
        return ApiResponse::success($analytics, 'Campaign analytics retrieved successfully');
    }

    public function destroy($id)
    {
        $this->campaignService->deleteCampaign($id);
        return ApiResponse::success(null, 'Campaign deleted successfully');
    }
}
