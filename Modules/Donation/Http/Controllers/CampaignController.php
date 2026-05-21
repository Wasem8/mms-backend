<?php

namespace Modules\Donation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Donation\Services\CampaignService;
use Modules\Donation\Http\Requests\StoreCampaignRequest;
use Modules\Donation\Http\Requests\UpdateCampaignRequest;
use Modules\Donation\Services\CampaignAnalyticsService;
use Illuminate\Support\Collection;

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
        if ($campaigns instanceof Collection) {
            $campaigns = $campaigns->map(function ($c) {
                return $this->addPercentageToCampaign($c);
            });
        }

        return ApiResponse::success($campaigns, 'Campaigns retrieved successfully');
    }

    public function stats(int $mosqueId)
    {
        $stats = $this->campaignService->getStatsByMosque($mosqueId);

        return ApiResponse::success($stats, 'Campaign statistics retrieved successfully');
    }

    public function show($id)
    {
        $campaign = $this->campaignService->getCampaignById($id);
        $campaign = $this->addPercentageToCampaign($campaign);
        return ApiResponse::success($campaign, 'Campaign retrieved successfully');
    }

    public function showByMosque($mosqueId)
    {
        $campaigns = $this->campaignService->getCampaignsByMosque($mosqueId);
        if ($campaigns instanceof Collection) {
            $campaigns = $campaigns->map(function ($c) {
                return $this->addPercentageToCampaign($c);
            });
        }

        return ApiResponse::success($campaigns, 'Campaigns retrieved successfully');
    }

    public function store(StoreCampaignRequest $request)
    {
        $data = $request->all();
        $campaign = $this->campaignService->createCampaign($data);
        $campaign = $this->addPercentageToCampaign($campaign);
        return ApiResponse::success($campaign, 'Campaign created successfully', 201);
    }

    public function update(UpdateCampaignRequest $request, $id)
    {
        $data = $request->all();
        $campaign = $this->campaignService->updateCampaign($id, $data);
        $campaign = $this->addPercentageToCampaign($campaign);
        return ApiResponse::success($campaign, 'Campaign updated successfully');
    }

    private function addPercentageToCampaign($campaign)
    {
        if (!$campaign) {
            return $campaign;
        }

        // Handle Eloquent models (single) — do not persist changes
        $collected = isset($campaign->collected_amount) ? (float) $campaign->collected_amount : 0.0;
        $target = isset($campaign->target_amount) ? (float) $campaign->target_amount : 0.0;

        $percentage = 0.0;
        if ($target > 0) {
            $percentage = round(($collected / $target) * 100, 1);
        }

        // Attach transient attribute (won't be saved unless explicitly persisted)
        $campaign->percentage = $percentage;

        return $campaign;
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
