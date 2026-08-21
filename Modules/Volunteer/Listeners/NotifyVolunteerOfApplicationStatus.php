<?php

namespace Modules\Volunteer\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Volunteer\Events\ApplicationStatusChanged;

class NotifyVolunteerOfApplicationStatus
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(ApplicationStatusChanged $event): void
    {
        $application = $event->application;
        $volunteer   = $application->volunteer;
        $status      = $application->status->value;

        if (!$volunteer) {
            return;
        }

        $this->notificationService->notify(
            $volunteer,
            'تحديث حالة طلب التطوع',
            'تم تحديث حالة طلبك في فرصة «' . $application->opportunity->title . '» إلى: ' . $status,
            'application_status_changed',
            [
                'application_id' => (string) $application->id,
                'opportunity_id' => (string) $application->opportunity_id,
                'status'         => $status,
            ]
        );
    }
}
