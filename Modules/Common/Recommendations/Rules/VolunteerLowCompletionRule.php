<?php

namespace Modules\Common\Recommendations\Rules;

use Modules\Common\Recommendations\DTO\MosqueStatsDTO;
use Modules\Common\Recommendations\DTO\RecommendationDTO;
use Modules\Common\Recommendations\Enums\Severity;
use Modules\Common\Recommendations\RecommendationRule;

class VolunteerLowCompletionRule implements RecommendationRule
{
    public function evaluate(MosqueStatsDTO $stats): ?RecommendationDTO
    {
        if ($stats->completionRate < 0.5) {
            return new RecommendationDTO(
                'volunteer',
                Severity::Warning,
                'نسبة إنجاز المهام التطوعية منخفضة — يُنصح بمراجعة توزيع المهام أو تحفيز المتطوعين',
            );
        }

        return null;
    }
}
