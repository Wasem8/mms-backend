<?php

namespace Modules\Common\Recommendations\Rules;

use Modules\Common\Recommendations\DTO\MosqueStatsDTO;
use Modules\Common\Recommendations\DTO\RecommendationDTO;
use Modules\Common\Recommendations\Enums\Severity;
use Modules\Common\Recommendations\RecommendationRule;

class ComplaintResolutionRule implements RecommendationRule
{
    public function evaluate(MosqueStatsDTO $stats): ?RecommendationDTO
    {
        if ($stats->avgResolutionDays > 7) {
            return new RecommendationDTO(
                'complaints',
                Severity::Warning,
                'متوسط زمن حل الشكاوى يتجاوز أسبوعاً — يُنصح بزيادة عدد المسؤولين المكلفين بالمتابعة',
            );
        }

        return null;
    }
}
