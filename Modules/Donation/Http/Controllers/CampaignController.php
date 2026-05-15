<?php

namespace Modules\Donation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Donation\Services\CampaignService;
use Modules\Donation\Http\Requests\StoreCampaignRequest;
use Modules\Donation\Http\Requests\UpdateCampaignRequest;
use Modules\Donation\Services\CampaignAnalyticsService;

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
        return ApiResponse::success($campaigns,'Campaigns retrieved successfully');
        }

    public function stats(int $mosqueId)
    {
        $stats = $this->campaignService->getStatsByMosque($mosqueId);

        return ApiResponse::success($stats, 'Campaign statistics retrieved successfully');
    }

    public function show($id)
    {
        $campaign = $this->campaignService->getCampaignById($id);
        return ApiResponse::success($campaign,'Campaign retrieved successfully');
    }

   public function showByMosque($mosqueId)
    {
        $campaigns = $this->campaignService->getCampaignsByMosque($mosqueId);
        return ApiResponse::success($campaigns,'Campaigns retrieved successfully');
    }

    public function store(StoreCampaignRequest $request)
    {
        $data = $request->all();
        $campaign = $this->campaignService->createCampaign($data);
        return ApiResponse::success($campaign, 'Campaign created successfully', 201);
    }

    public function update(UpdateCampaignRequest $request, $id)
    {
        $data = $request->all();
        $campaign = $this->campaignService->updateCampaign($id, $data);
        return ApiResponse::success($campaign, 'Campaign updated successfully');
    }

    public function destroy($id)
    {
        $this->campaignService->deleteCampaign($id);
        return ApiResponse::success(null, 'Campaign deleted successfully');
    }

    public function analytics(int $id)
    {
        $campaign  = $this->campaignService->getCampaignById($id);
        $analytics = $this->analyticsService->getAnalytics($campaign);

        return ApiResponse::success($analytics, 'Campaign analytics retrieved successfully');
    }

}
