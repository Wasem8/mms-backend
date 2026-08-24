<?php

namespace Modules\Donation\Repositories;

use Modules\Donation\Models\Campaign;

class CampaignRepository implements CampaignRepositoryInterface
{
    public function all()
    {
        return Campaign::all();
    }

    public function find($id)
    {
        return Campaign::with('mosque')->findOrFail($id);
    }

    public function findByMosque($mosqueId)
    {

        return Campaign::where('mosque_id', $mosqueId)->get();
    }

    public function create(array $data)
    {
        return Campaign::create($data);
    }

    public function update($id, array $data)
    {
        $campaign = $this->find($id);
        $campaign->update($data);
        return $campaign;
    }

    public function delete($id)
    {
        $campaign = $this->find($id);
        return $campaign->delete();
    }

    public function getFiltered(array $filters = [])
    {
        $query = Campaign::query()->with('mosque');

        if (isset($filters['mosque_id'])) {
            $query->where('mosque_id', $filters['mosque_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSortFields = ['created_at', 'start_date', 'end_date', 'target_amount', 'collected_amount', 'title', 'status'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');

        $perPage = isset($filters['per_page']) ? max(1, min(100, (int) $filters['per_page'])) : 15;

        return $query->paginate($perPage);
    }

    public function expirePastEndDateCampaigns()
    {
        return Campaign::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['status' => 'completed']);
    }

    public function getStatsByMosque(int $mosqueId): array
    {
        $stats = Campaign::where('mosque_id', $mosqueId)
            ->selectRaw("
                COALESCE(SUM(collected_amount), 0)                                          AS total_collected,
                COUNT(CASE WHEN status = 'active'    THEN 1 END)                            AS active_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END)                            AS completed_count,
                COALESCE(SUM(CASE WHEN status = 'active' THEN collected_amount END), 0)     AS active_collected,
                COALESCE(SUM(CASE WHEN status = 'active' THEN target_amount    END), 0)     AS active_target
            ")
            ->first();

        // Month-on-month growth: compare this month's collected vs last month's
        $thisMonth = Campaign::where('mosque_id', $mosqueId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('collected_amount');

        $lastMonth = Campaign::where('mosque_id', $mosqueId)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('collected_amount');

        $growthRate = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100.0 : 0.0);

        return [
            'total_collected'     => (float) $stats->total_collected,
            'active_count'        => (int)   $stats->active_count,
            'completed_count'     => (int)   $stats->completed_count,
            'growth_rate_percent' => $growthRate,
        ];
    }

    public function getStatsForAll(): array
    {
        $this->expirePastEndDateCampaigns();

        $totals = Campaign::query()
            ->selectRaw("
                COUNT(*)                                                              AS total_campaigns,
                COALESCE(SUM(collected_amount), 0)                                    AS total_collected,
                COALESCE(SUM(target_amount), 0)                                       AS total_target,
                COUNT(CASE WHEN status = 'active'    THEN 1 END)                      AS active_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END)                      AS completed_count,
                COUNT(CASE WHEN status = 'paused'    THEN 1 END)                      AS paused_count,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END)                      AS cancelled_count,
                COUNT(DISTINCT mosque_id)                                             AS mosques_count
            ")
            ->first();

        $growthRate = $this->growthRateFor(Campaign::query());

        $overallProgress = $totals->total_target > 0
            ? round(($totals->total_collected / $totals->total_target) * 100, 1)
            : 0.0;

        $perMosque = Campaign::query()
            ->select('mosque_id')
            ->selectRaw("
                COUNT(*)                                                              AS total_campaigns,
                COALESCE(SUM(collected_amount), 0)                                    AS total_collected,
                COALESCE(SUM(target_amount), 0)                                       AS total_target,
                COUNT(CASE WHEN status = 'active'    THEN 1 END)                      AS active_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END)                      AS completed_count
            ")
            ->groupBy('mosque_id')
            ->with('mosque:id,name,city_id')
            ->with('mosque.city:id,name_ar,name_en')
            ->orderByDesc('total_collected')
            ->get()
            ->map(function ($row) {
                $mosque   = $row->mosque;
                $city     = $mosque && $mosque->relationLoaded('city') ? $mosque->getRelation('city') : null;
                $locale   = app()->getLocale();
                $target   = (float) $row->total_target;
                $collected = (float) $row->total_collected;

                return [
                    'mosque_id'        => $row->mosque_id,
                    'mosque_name'      => $mosque?->name,
                    'city'             => $city ? ($locale === 'ar' ? $city->name_ar : $city->name_en) : null,
                    'total_campaigns'  => (int) $row->total_campaigns,
                    'active_count'     => (int) $row->active_count,
                    'completed_count'  => (int) $row->completed_count,
                    'total_collected'  => $collected,
                    'total_target'     => $target,
                    'progress_percent' => $target > 0 ? round(($collected / $target) * 100, 1) : 0.0,
                ];
            });

        return [
            'total_campaigns'           => (int)    $totals->total_campaigns,
            'total_collected'           => (float) $totals->total_collected,
            'total_target'              => (float) $totals->total_target,
            'overall_progress_percent'  => $overallProgress,
            'active_count'              => (int)    $totals->active_count,
            'completed_count'           => (int)    $totals->completed_count,
            'growth_rate_percent'       => $growthRate,
            'mosques_with_campaigns'   => (int)    $totals->mosques_count,
            'status_breakdown'          => [
                'active'    => (int) $totals->active_count,
                'completed' => (int) $totals->completed_count,
                'paused'    => (int) $totals->paused_count,
                'cancelled' => (int) $totals->cancelled_count,
            ],
            'per_mosque'                => $perMosque->toArray(),
        ];
    }

    private function growthRateFor(\Illuminate\Database\Eloquent\Builder $query): float
    {
        $thisMonth = (clone $query)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('collected_amount');

        $lastMonth = (clone $query)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('collected_amount');

        return $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100.0 : 0.0);
    }
}
