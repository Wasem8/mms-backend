<?php

namespace Modules\Complaint\Repositories;

use Modules\Complaint\Models\Complaint;

class ComplaintStatsRepository implements ComplaintStatsRepositoryInterface
{
    public function getAvgResolutionDays(int $mosqueId): float
    {
        $complaints = Complaint::query()
            ->where('mosque_id', $mosqueId)
            ->where('status', 'resolved')
            ->with([
                'statusLogs' => function ($query) {
                    $query->where('new_status', 'resolved')->orderBy('changed_at', 'asc');
                },
            ])
            ->get();

        $days = [];

        foreach ($complaints as $complaint) {
            $log = $complaint->statusLogs->first();

            if ($log && $complaint->created_at && $log->changed_at) {
                $days[] = $complaint->created_at->diffInDays($log->changed_at);
            }
        }

        if (empty($days)) {
            return 0.0;
        }

        return round(array_sum($days) / count($days), 2);
    }
}
