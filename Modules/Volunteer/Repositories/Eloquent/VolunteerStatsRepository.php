<?php

namespace Modules\Volunteer\Repositories\Eloquent;

use Modules\Volunteer\Enums\TaskStatus;
use Modules\Volunteer\Models\VolunteerTask;
use Modules\Volunteer\Repositories\Contracts\VolunteerStatsRepositoryInterface;

class VolunteerStatsRepository implements VolunteerStatsRepositoryInterface
{
    public function getCompletionRate(int $mosqueId): float
    {
        $total = VolunteerTask::query()
            ->whereHas('opportunity', function ($query) use ($mosqueId) {
                $query->where('mosque_id', $mosqueId);
            })
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $completed = VolunteerTask::query()
            ->whereHas('opportunity', function ($query) use ($mosqueId) {
                $query->where('mosque_id', $mosqueId);
            })
            ->where('status', TaskStatus::Completed)
            ->count();

        return round($completed / $total, 4);
    }
}
