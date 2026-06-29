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
        return Campaign::findOrFail($id);
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
        $query = Campaign::query();

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
}
