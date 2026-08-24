<?php

namespace Modules\Common\Recommendations;

use Modules\Common\Recommendations\DTO\MosqueStatsDTO;
use Modules\Common\Recommendations\DTO\RecommendationDTO;

interface RecommendationRule
{
    public function evaluate(MosqueStatsDTO $stats): ?RecommendationDTO;
}
