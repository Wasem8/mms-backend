<?php

namespace Modules\Common\Recommendations\Rules;

use Modules\Common\Recommendations\DTO\MosqueStatsDTO;
use Modules\Common\Recommendations\DTO\RecommendationDTO;
use Modules\Common\Recommendations\Enums\Severity;
use Modules\Common\Recommendations\RecommendationRule;

class MaintenanceBacklogRule implements RecommendationRule
{
    public function evaluate(MosqueStatsDTO $stats): ?RecommendationDTO
    {
        if ($stats->pendingCount > 3 && $stats->avgAgeDays > 14) {
            return new RecommendationDTO(
                'maintenance',
                Severity::Warning,
                'توجد طلبات صيانة متراكمة لأكثر من أسبوعين — يوصى بجدولة زيارة صيانة عاجلة',
            );
        }

        return null;
    }
}
