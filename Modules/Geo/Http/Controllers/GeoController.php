<?php

namespace Modules\Geo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Geo\Service\GeoService;
use App\Support\ApiResponse;
use Modules\Geo\Http\Resources\GovernorateResource;



class GeoController extends Controller
{
    public function __construct(
        private readonly GeoService $geoService,
    ) {}

    public function index()
    {
        $tree = $this->geoService->getCatalog();

        return ApiResponse::success($tree, 'Geo Catalog retrieved successfully');
    }
    


}
