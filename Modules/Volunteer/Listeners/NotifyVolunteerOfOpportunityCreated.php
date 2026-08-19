<?php

namespace Modules\Volunteer\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\User\Models\User;
use Modules\Volunteer\Events\OpportunityCreated;

class NotifyVolunteerOfOpportunityCreated
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(OpportunityCreated $event): void
    {
        $opportunity = $event->opportunity;

        // Notify only volunteers belonging to the same mosque as the opportunity.
        $volunteers = User::where('mosque_id', $opportunity->mosque_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'volunteer'))
            ->get();

        foreach ($volunteers as $volunteer) {
            $this->notificationService->notify(
                $volunteer,
                'فرصة تطوعية جديدة في مسجدك',
                'تمت إضافة فرصة تطوعية: ' . $opportunity->title,
                'opportunity_created',
                ['opportunity_id' => (string) $opportunity->id]
            );
        }
    }
}
