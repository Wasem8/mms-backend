<?php

namespace Modules\Donation\Repositories;

use Modules\Donation\Models\Campaign;

class DonationStatsRepository implements DonationStatsRepositoryInterface
{
    public function getCampaignProgress(int $mosqueId): float
    {
        $campaigns = Campaign::query()
            ->where('mosque_id', $mosqueId)
            ->where('target_amount', '>', 0)
            ->get();

        if ($campaigns->isEmpty()) {
            return 0.0;
        }

        $progress = $campaigns->map(function (Campaign $campaign): float {
            $target = (float) $campaign->target_amount;

            if ($target <= 0) {
                return 0.0;
            }

            return min(1.0, (float) $campaign->collected_amount / $target);
        });

        return round((float) $progress->max(), 4);
    }
}
