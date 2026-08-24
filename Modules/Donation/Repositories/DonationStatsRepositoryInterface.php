<?php

namespace Modules\Donation\Repositories;

interface DonationStatsRepositoryInterface
{
    /**
     * Highest campaign progress (collected / target) for the mosque, between 0.0 and 1.0.
     */
    public function getCampaignProgress(int $mosqueId): float;
}
