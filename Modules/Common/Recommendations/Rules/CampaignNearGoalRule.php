<?php

namespace Modules\Common\Recommendations\Rules;

use Modules\Common\Recommendations\DTO\MosqueStatsDTO;
use Modules\Common\Recommendations\DTO\RecommendationDTO;
use Modules\Common\Recommendations\Enums\Severity;
use Modules\Common\Recommendations\RecommendationRule;

class CampaignNearGoalRule implements RecommendationRule
{
    public function evaluate(MosqueStatsDTO $stats): ?RecommendationDTO
    {
        if ($stats->campaignProgress > 0.9) {
            return new RecommendationDTO(
                'donation',
                Severity::Info,
                'الحملة قاربت على تحقيق هدفها — فرصة جيدة لتكثيف الترويج في الأيام الأخيرة',
            );
        }

        return null;
    }
}
