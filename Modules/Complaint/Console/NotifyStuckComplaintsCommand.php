<?php

namespace Modules\Complaint\Console;

use Illuminate\Console\Command;
use Modules\Common\Models\Notification as NotificationModel;
use Modules\Common\Services\NotificationService;
use Modules\Complaint\Models\Complaint;
use Modules\User\Models\User;

class NotifyStuckComplaintsCommand extends Command
{
    /**
     * المدة المسموحة قبل إشعار مدير المنطقة (بالأيام).
     */
    private const STUCK_DAYS = 3;

    /**
     * الحالة المبدئية للشكوى التي لم تتغير.
     */
    private const INITIAL_STATUS = 'pending';

    protected $signature = 'complaints:notify-stuck';

    protected $description = 'Notify area managers (super_admin) about complaints stuck in their initial status for more than 3 days.';

    public function handle(NotificationService $notificationService): void
    {
        $threshold = now()->subDays(self::STUCK_DAYS);

        $stuckComplaints = Complaint::query()
            ->where('status', self::INITIAL_STATUS)
            ->where('created_at', '<=', $threshold)
            // التحقق فعلياً من أن الحالة المبدئية لم تتغيّر: لا يوجد سجلّ تغيير
            // حقيقي (old_status يختلف عن new_status). الإسناد لمشرف يسجّل سجلّاً
            // بقيمتين متطابقتين فيبقى معتبراً عالقاً.
            ->whereDoesntHave('statusLogs', function ($q) {
                $q->whereColumn('old_status', '<>', 'new_status');
            })
            ->get();

        if ($stuckComplaints->isEmpty()) {
            $this->info('No stuck complaints found.');

            return;
        }

        $areaManagers = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
            ->get();

        if ($areaManagers->isEmpty()) {
            $this->warn('No area managers (super_admin) found to notify.');

            return;
        }

        $notified = 0;

        foreach ($stuckComplaints as $complaint) {
            if ($this->alreadyNotified($complaint->id)) {
                continue;
            }

            $mosqueName = $complaint->mosque?->name ?? 'المسجد';

            foreach ($areaManagers as $manager) {
                $notificationService->notify(
                    $manager,
                    'شكوى معلّقة منذ 3 أيام',
                    "الشكوى رقم {$complaint->complaint_number} المقدمة على مسجد {$mosqueName} مرّ عليها أكثر من 3 أيام ولا تزال بحالتها المبدئية دون معالجة.",
                    'complaint_stuck',
                    [
                        'complaint_id'     => (string) $complaint->id,
                        'complaint_number' => (string) $complaint->complaint_number,
                        'mosque_id'        => (string) $complaint->mosque_id,
                        'created_at'       => (string) $complaint->created_at,
                    ]
                );
            }

            $notified++;
        }

        $this->info("System check completed. {$notified} stuck complaint(s) reported to area managers.");
    }

    private function alreadyNotified(int $complaintId): bool
    {
        return NotificationModel::where('type', 'complaint_stuck')
            ->whereJsonContains('data', ['complaint_id' => (string) $complaintId])
            ->exists();
    }
}
