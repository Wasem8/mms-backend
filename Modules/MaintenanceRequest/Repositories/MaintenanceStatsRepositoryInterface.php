<?php

namespace Modules\MaintenanceRequest\Repositories;

interface MaintenanceStatsRepositoryInterface
{
    /**
     * @return array{count: int, avg_age_days: float}
     */
    public function getPendingStats(int $mosqueId): array;
}
