<?php

namespace Modules\Common\Recommendations;

use Illuminate\Support\Facades\Cache;
use Modules\Common\Recommendations\DTO\MosqueStatsDTO;
use Modules\Complaint\Repositories\ComplaintStatsRepositoryInterface;
use Modules\MaintenanceRequest\Repositories\MaintenanceStatsRepositoryInterface;
use Modules\Volunteer\Repositories\Contracts\VolunteerStatsRepositoryInterface;
use Modules\Donation\Repositories\DonationStatsRepositoryInterface;

class StatsCollectorService
{
    public function __construct(
        private ComplaintStatsRepositoryInterface $complaintStats,
        private MaintenanceStatsRepositoryInterface $maintenanceStats,
        private VolunteerStatsRepositoryInterface $volunteerStats,
        private DonationStatsRepositoryInterface $donationStats,
    ) {}

    public function collect(int $mosqueId): MosqueStatsDTO
    {
        $data = Cache::remember(
            "mosque_stats:{$mosqueId}",
            now()->addHours(6),
            function () use ($mosqueId): array {
                $pending = $this->maintenanceStats->getPendingStats($mosqueId);

                return [
                    'avgResolutionDays' => $this->complaintStats->getAvgResolutionDays($mosqueId),
                    'pendingCount'      => (int) ($pending['count'] ?? 0),
                    'avgAgeDays'        => (float) ($pending['avg_age_days'] ?? 0.0),
                    'completionRate'    => $this->volunteerStats->getCompletionRate($mosqueId),
                    'campaignProgress'  => $this->donationStats->getCampaignProgress($mosqueId),
                ];
            }
        );

        return new MosqueStatsDTO(
            avgResolutionDays: $data['avgResolutionDays'],
            pendingCount: $data['pendingCount'],
            avgAgeDays: $data['avgAgeDays'],
            completionRate: $data['completionRate'],
            campaignProgress: $data['campaignProgress'],
        );
    }
}
