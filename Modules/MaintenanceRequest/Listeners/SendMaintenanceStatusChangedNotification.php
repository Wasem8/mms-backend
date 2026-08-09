<?php

namespace Modules\MaintenanceRequest\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\MaintenanceRequest\Events\MaintenanceStatusChanged;
use Modules\User\Models\User;

class SendMaintenanceStatusChangedNotification
{
    private const STATUS_LABELS = [
        'pending'     => 'قيد الانتظار',
        'in_progress' => 'قيد التنفيذ',
        'completed'   => 'مكتمل',
        'cancelled'   => 'ملغى',
    ];

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(MaintenanceStatusChanged $event): void
    {
        $maintenance = $event->maintenance;
        $newStatusLabel = self::STATUS_LABELS[$event->newStatus] ?? $event->newStatus;

        $body = "تم تحديث حالة طلب الصيانة رقم {$maintenance->maintenance_number} إلى \"{$newStatusLabel}\".";
        if ($event->note) {
            $body .= " ملاحظة: {$event->note}";
        }

        $data = [
            'maintenance_id'     => (string) $maintenance->id,
            'maintenance_number' => (string) $maintenance->maintenance_number,
            'old_status'         => (string) $event->oldStatus,
            'new_status'         => (string) $event->newStatus,
        ];

        // اجمع المستلمين وامنع التكرار لو نفس الشخص (مدير سوّى الطلب بنفسه)
        $recipientIds = array_unique(array_filter([
            $maintenance->requested_by,
            $maintenance->mosque?->manager_id,
        ]));

        $recipients = User::whereIn('id', $recipientIds)->get();

        foreach ($recipients as $recipient) {
            $this->notificationService->notify(
                $recipient,
                'تحديث حالة طلب الصيانة',
                $body,
                'maintenance_status_changed',
                $data
            );
        }
    }
}
