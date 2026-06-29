<?php

namespace Modules\MaintenanceRequest\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MaintenanceRequest\Models\Maintenance;

class MaintenanceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $maintenances = [
            [
                'maintenance_number' => 'MNT-00001',
                'mosque_id' => 1,
                'title' => 'إصلاح الأسلاك الكهربائية',
                'description' => 'وجود أسلاك كهربائية مكشوفة في قاعة الصلاة الرئيسية',
                'category' => 'electrical',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'requested_by' => 2,
                'scheduled_at' => '2025-06-01 08:00:00',
                'completed_at' => null,
                'notes' => 'يتطلب فني كهرباء متخصص',
            ],
            [
                'maintenance_number' => 'MNT-00002',
                'mosque_id' => 1,
                'title' => 'تسريب مياه في دورات المياه',
                'description' => 'تسريب مياه من المواسير في دورات المياه',
                'category' => 'plumbing',
                'priority' => 'high',
                'status' => 'pending',
                'requested_by' => 4,
                'scheduled_at' => null,
                'completed_at' => null,
                'notes' => null,
            ],
            [
                'maintenance_number' => 'MNT-00003',
                'mosque_id' => 2,
                'title' => 'تنظيف عام للمسجد',
                'description' => 'حملة تنظيف شاملة للمسجد وجميع مرافقه',
                'category' => 'cleaning',
                'priority' => 'low',
                'status' => 'completed',
                'requested_by' => 2,
                'scheduled_at' => '2025-05-10 06:00:00',
                'completed_at' => '2025-05-10 18:00:00',
                'notes' => 'تم التنظيف بالتعاون مع المتطوعين',
            ],
            [
                'maintenance_number' => 'MNT-00004',
                'mosque_id' => 3,
                'title' => 'إصلاح باب المصلى',
                'description' => 'الباب الرئيسي للمصلى لا يغلق بشكل صحيح',
                'category' => 'carpentry',
                'priority' => 'medium',
                'status' => 'pending',
                'requested_by' => 5,
                'scheduled_at' => null,
                'completed_at' => null,
                'notes' => null,
            ],
            [
                'maintenance_number' => 'MNT-00005',
                'mosque_id' => 4,
                'title' => 'استبدال لمبات الإضاءة',
                'description' => 'عدد من لمبات الإضاءة بحاجة للاستبدال',
                'category' => 'electrical',
                'priority' => 'low',
                'status' => 'cancelled',
                'requested_by' => 2,
                'scheduled_at' => '2025-04-01 09:00:00',
                'completed_at' => null,
                'notes' => 'تم إلغاء الطلب بسبب نقص القطع',
            ],
            [
                'maintenance_number' => 'MNT-00006',
                'mosque_id' => 5,
                'title' => 'صيانة مكيفات الهواء',
                'description' => 'مكيفات الهواء لا تعمل بكفاءة وتحتاج صيانة',
                'category' => 'other',
                'priority' => 'medium',
                'status' => 'pending',
                'requested_by' => 2,
                'scheduled_at' => null,
                'completed_at' => null,
                'notes' => null,
            ],
        ];

        foreach ($maintenances as $maintenance) {
            Maintenance::create($maintenance);
        }
    }
}
