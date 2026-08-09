<?php

namespace Modules\Complaint\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\Complaint\Events\ComplaintStatusChanged;

class SendComplaintStatusChangedNotification
{
    private const STATUS_LABELS = [
        'pending'     => 'قيد الانتظار',
        'in_progress' => 'قيد المعالجة',
        'resolved'    => 'تم الحل',
        'canceled'    => 'ملغاة',
    ];

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(ComplaintStatusChanged $event): void
    {
        $complaint = $event->complaint;
        $newStatusLabel = self::STATUS_LABELS[$event->newStatus] ?? $event->newStatus;

        $body = "تم تحديث حالة شكواك رقم {$complaint->complaint_number} إلى \"{$newStatusLabel}\".";
        if ($event->note) {
            $body .= " ملاحظة: {$event->note}";
        }

        $data = [
            'complaint_id'     => (string) $complaint->id,
            'complaint_number' => (string) $complaint->complaint_number,
            'old_status'       => (string) $event->oldStatus,
            'new_status'       => (string) $event->newStatus,
        ];

        // Notify the submitter, unless the complaint is anonymous (no linked user account)
        if (!$complaint->is_anonymous && $complaint->user) {
            $this->notificationService->notify(
                $complaint->user,
                'تحديث حالة الشكوى',
                $body,
                'complaint_status_changed',
                $data
            );
        }

        // Notify the mosque manager too
        $manager = $complaint->mosque?->manager;
        if ($manager) {
            $this->notificationService->notify(
                $manager,
                'تحديث حالة شكوى',
                "تم تحديث حالة الشكوى رقم {$complaint->complaint_number} إلى \"{$newStatusLabel}\".",
                'complaint_status_changed',
                $data
            );
        }
    }
}
