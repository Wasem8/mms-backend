<?php

namespace Modules\Donation\Services;

use Modules\Donation\Models\Campaign;
use Modules\Donation\Models\Donation;

class CampaignAnalyticsService
{
    public function getAnalytics(Campaign $campaign): array
    {
        $base = Donation::query()
            ->where('campaign_id', $campaign->id)
            ->where('status', 'completed')
            ->where('donation_type', 'cash');

        $aggregates = (clone $base)
            ->selectRaw('COUNT(*) as total_count, COUNT(DISTINCT donor_name) as unique_donors, COALESCE(AVG(amount), 0) as avg_amount')
            ->first();

        return [
            'weekly_growth_percent' => $this->weeklyGrowth($campaign->id),
            'avg_donation_amount'   => (int) round($aggregates->avg_amount),
            'unique_donors_count'   => (int) $aggregates->unique_donors,
            'total_donations_count' => (int) $aggregates->total_count,
        ];
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function weeklyGrowth(int $campaignId): float
    {
        $completed = fn() => Donation::where('campaign_id', $campaignId)
            ->where('status', 'completed')
            ->where('type', 'cash');

        $thisWeek = (clone $completed())
            ->where('created_at', '>=', now()->startOfWeek())
            ->sum('amount');

        $lastWeek = (clone $completed())
            ->whereBetween('created_at', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek(),
            ])
            ->sum('amount');

        if ($lastWeek == 0) {
            return $thisWeek > 0 ? 100.0 : 0.0;
        }

        return round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1);
    }
}
