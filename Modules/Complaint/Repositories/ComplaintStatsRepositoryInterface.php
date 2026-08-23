<?php

namespace Modules\Complaint\Repositories;

interface ComplaintStatsRepositoryInterface
{
    public function getAvgResolutionDays(int $mosqueId): float;
}
