<?php

namespace Modules\Complaint\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Complaint\Events\ComplaintSubmitted;

class SendComplaintSubmittedNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(ComplaintSubmitted $event): void
    {
        $complaint = $event->complaint;
        $manager = $complaint->mosque?->manager;

        if (!$manager) {
            return;
        }

        $this->notificationService->notify(
            $manager,
            'شكوى جديدة',
            "تم تقديم شكوى جديدة برقم {$complaint->complaint_number} على مسجدك.",
            'complaint_submitted',
            [
                'complaint_id'     => (string) $complaint->id,
                'complaint_number' => (string) $complaint->complaint_number,
                'mosque_id'        => (string) $complaint->mosque_id,
            ]
        );
    }
}
