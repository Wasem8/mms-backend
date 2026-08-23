<?php

namespace Modules\MaintenanceRequest\Console;

use Illuminate\Console\Command;
use Modules\Common\Models\Notification as NotificationModel;
use Modules\Common\Services\NotificationService;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\User\Models\User;

class NotifyDelayedMaintenanceCommand extends Command
{
    /**
     * مدة التأخير المسموحة قبل إشعار مدير المنطقة (بالأيام).
     */
    private const DELAY_DAYS = 7;

    protected $signature = 'maintenance:notify-delayed';

    protected $description = 'Notify area managers (super_admin) about maintenance requests delayed more than a week from their creation date.';

    public function handle(NotificationService $notificationService): void
    {
        $threshold = now()->subDays(self::DELAY_DAYS);

        $delayedRequests = Maintenance::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNull('deleted_at')
            ->where('created_at', '<=', $threshold)
            ->get();

        if ($delayedRequests->isEmpty()) {
            $this->info('No delayed maintenance requests found.');

            return;
        }

        $areaManagers = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
            ->get();

        if ($areaManagers->isEmpty()) {
            $this->warn('No area managers (super_admin) found to notify.');

            return;
        }

        $notified = 0;

        foreach ($delayedRequests as $maintenance) {
            if ($this->alreadyNotified($maintenance->id)) {
                continue;
            }

            $mosqueName = $maintenance->mosque?->name ?? 'المسجد';

            foreach ($areaManagers as $manager) {
                $notificationService->notify(
                    $manager,
                    'تأخر طلب صيانة',
                    "طلب الصيانة رقم {$maintenance->maintenance_number} لمسجد {$mosqueName} تأخر أكثر من أسبوع من تاريخ إنشائه ولا يزال قيد المعالجة.",
                    'maintenance_delayed',
                    [
                        'maintenance_id'     => (string) $maintenance->id,
                        'maintenance_number' => (string) $maintenance->maintenance_number,
                        'mosque_id'          => (string) $maintenance->mosque_id,
                        'created_at'         => (string) $maintenance->created_at,
                    ]
                );
            }

            $notified++;
        }

        $this->info("System check completed. {$notified} delayed maintenance request(s) reported to area managers.");
    }

    private function alreadyNotified(int $maintenanceId): bool
    {
        return NotificationModel::where('type', 'maintenance_delayed')
            ->whereJsonContains('data', ['maintenance_id' => (string) $maintenanceId])
            ->exists();
    }
}
