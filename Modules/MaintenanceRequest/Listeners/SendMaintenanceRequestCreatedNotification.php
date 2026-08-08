<?php

namespace Modules\MaintenanceRequest\Listeners;

use Modules\Common\Services\NotificationService;
use Modules\MaintenanceRequest\Events\MaintenanceRequestCreated;

class SendMaintenanceRequestCreatedNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(MaintenanceRequestCreated $event): void
    {
        $maintenance = $event->maintenance;
        $manager = $maintenance->mosque?->manager;

        // لا ترسل إشعار لو مدير المسجد هو نفسه مقدّم الطلب
        if (!$manager || $manager->id === $maintenance->requested_by) {
            return;
        }

        $this->notificationService->notify(
            $manager,
            'طلب صيانة جديد',
            "تم تقديم طلب صيانة جديد برقم {$maintenance->maintenance_number} لمسجدك.",
            'maintenance_request_created',
            [
                'maintenance_id'     => (string) $maintenance->id,
                'maintenance_number' => (string) $maintenance->maintenance_number,
                'mosque_id'          => (string) $maintenance->mosque_id,
            ]
        );
    }
}
