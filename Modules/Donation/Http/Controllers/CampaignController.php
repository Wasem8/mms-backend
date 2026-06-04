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

class CampaignController extends Controller
{

    protected $campaignService;
    protected $analyticsService;

    public function __construct(CampaignService $campaignService, CampaignAnalyticsService $analyticsService)
    {
        $this->campaignService = $campaignService;
        $this->analyticsService = $analyticsService;
    }
    public function index()
    {
        $campaigns = $this->campaignService->getAllCampaigns();
        return ApiResponse::success(CampaignResource::collection($campaigns), 'Campaigns retrieved successfully');
    }

    public function show($id)
    {

        $campaign = $this->campaignService->getCampaignById($id);
        return ApiResponse::success(new CampaignResource($campaign), 'Campaign retrieved successfully');
    }

    public function showByMosque($mosqueId)
    {
       // dd('أنا داخل الكنترولر، ورقم المسجد الممرر هو: ' . $mosqueId);
        $campaigns = $this->campaignService->getCampaignsByMosque($mosqueId);
        return ApiResponse::success(CampaignResource::collection($campaigns), 'Campaigns retrieved successfully');
    }

    public function store(StoreCampaignRequest $request)
    {
        $campaign = $this->campaignService->createCampaign($request->all());
        return ApiResponse::success(new CampaignResource($campaign), 'Campaign created successfully', 201);
    }

    public function update(UpdateCampaignRequest $request, $id)
    {
        $campaign = $this->campaignService->updateCampaign($id, $request->all());
        return ApiResponse::success(new CampaignResource($campaign), 'Campaign updated successfully');
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
