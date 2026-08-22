<?php

namespace Modules\Complaint\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Complaint\Events\ComplaintAssigned;
use Modules\Common\Services\NotificationService;

class SendComplaintAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ComplaintAssigned $event)
    {
        $notificationService = new NotificationService();

        $title = 'شكوى جديدة مسندة إليك';
        $body = "تم إسناد الشكوى رقم {$event->complaint->complaint_number} إليك للمتابعة.";

        $notificationService->notify(
            $event->assignedAdmin,
            $title,
            $body,
            'complaint_assigned',
            [
                'complaint_id' => (string) $event->complaint->id,
                'complaint_number' => (string) $event->complaint->complaint_number,
                'assigned_by' => (string) $event->assignedBy,
            ]
        );
    }
}
