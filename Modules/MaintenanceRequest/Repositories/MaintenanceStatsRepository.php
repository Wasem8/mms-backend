<?php

namespace Modules\MaintenanceRequest\Repositories;

use Carbon\Carbon;
use Modules\MaintenanceRequest\Models\Maintenance;

class MaintenanceStatsRepository implements MaintenanceStatsRepositoryInterface
{
    public function getPendingStats(int $mosqueId): array
    {
        $items = Maintenance::query()
            ->where('mosque_id', $mosqueId)
            ->where('status', 'pending')
            ->get();

        $count = $items->count();

        if ($count === 0) {
            return ['count' => 0, 'avg_age_days' => 0.0];
        }

        $ages = $items->map(function (Maintenance $maintenance): float {
            return $maintenance->created_at
                ? (float) $maintenance->created_at->diffInDays(Carbon::now())
                : 0.0;
        });

        return [
            'count'         => $count,
            'avg_age_days'  => round((float) $ages->average(), 2),
        ];
    }
}
