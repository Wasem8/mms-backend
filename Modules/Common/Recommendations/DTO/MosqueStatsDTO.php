<?php

namespace Modules\Common\Recommendations\DTO;

readonly class MosqueStatsDTO
{
    public function __construct(
        public float $avgResolutionDays,
        public int $pendingCount,
        public float $avgAgeDays,
        public float $completionRate,
        public float $campaignProgress,
    ) {}
}
