<?php

namespace Modules\MaintenanceRequest\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\MaintenanceRequest\Models\MaintenanceStatusLog;
use Modules\User\Models\User;

class MaintenanceStatusLogSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        foreach (Maintenance::all() as $maintenance) {
            // تجنب التكرار عند إعادة البذر
            if (MaintenanceStatusLog::where('maintenance_id', $maintenance->id)->exists()) {
                continue;
            }

            $changedBy = User::find($maintenance->requested_by)?->name ?? 'مدير المسجد';

            // السجل الأول: إنشاء الطلب
            MaintenanceStatusLog::create([
                'maintenance_id' => $maintenance->id,
                'old_status' => null,
                'new_status' => 'pending',
                'changed_by' => $changedBy,
                'notes' => 'تم إنشاء طلب الصيانة من قبل ' . $changedBy,
                'created_at' => $maintenance->scheduled_at ?? $maintenance->created_at,
            ]);

            $count++;

            // سجلات الانتقال حسب الحالة الحالية
            $transitions = $this->transitionsFor($maintenance->status);

            foreach ($transitions as [$from, $to, $note]) {
                MaintenanceStatusLog::create([
                    'maintenance_id' => $maintenance->id,
                    'old_status' => $from,
                    'new_status' => $to,
                    'changed_by' => $changedBy,
                    'notes' => $note,
                    'created_at' => $maintenance->completed_at ?? $maintenance->scheduled_at ?? $maintenance->created_at,
                ]);

                $count++;
            }
        }

        $this->command->info('تم إنشاء/تحديث ' . $count . ' سجل حالة لطلبات الصيانة.');
    }

    /**
     * انتقالات الحالة من "قيد الانتظار" حتى الحالة الحالية.
     */
    private function transitionsFor(string $status): array
    {
        return match ($status) {
            'in_progress' => [
                ['pending', 'in_progress', 'بدأ العمل من قبل الفريق المختص'],
            ],
            'completed' => [
                ['pending', 'in_progress', 'بدأ العمل من قبل الفريق المختص'],
                ['in_progress', 'completed', 'تم الانتهاء من أعمال الصيانة بنجاح'],
            ],
            'cancelled' => [
                ['pending', 'cancelled', 'تم إلغاء الطلب لعدم توفر المتطلبات'],
            ],
            default => [], // pending: لا انتقالات إضافية
        };
    }
}
