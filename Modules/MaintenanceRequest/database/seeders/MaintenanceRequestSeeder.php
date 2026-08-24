<?php

namespace Modules\MaintenanceRequest\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class MaintenanceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $requester = User::whereHas('roles', fn($q) => $q->where('name', 'mosque_manager'))
            ->first()
            ?? User::first();

        if (!$requester) {
            $this->command->warn('لا يوجد مستخدم لإنشاء طلبات الصيانة، تم تخطي البذر.');

            return;
        }

        // [العنوان, الوصف, التصنيف, الأولوية, الحالة, ملاحظات]
        $templates = [
            ['إصلاح الأسلاك الكهربائية المكشوفة', 'وجود أسلاك كهربائية مكشوفة في قاعة الصلاة الرئيسية', 'electrical', 'urgent', 'in_progress', 'يتطلب فني كهرباء متخصص'],
            ['تسريب مياه في دورات المياه', 'تسريب مياه من المواسير في دورات المياه', 'plumbing', 'high', 'pending', null],
            ['تنظيف عام للمسجد ومرافقه', 'حملة تنظيف شاملة للمسجد وجميع مرافقه', 'cleaning', 'low', 'completed', 'تم التنظيف بالتعاون مع المتطوعين'],
            ['إصلاح باب المصلى الرئيسي', 'الباب الرئيسي للمصلى لا يغلق بشكل صحيح', 'carpentry', 'medium', 'pending', null],
            ['استبدال لمبات الإضاءة', 'عدد من لمبات الإضاءة بحاجة للاستبدال', 'electrical', 'low', 'cancelled', 'تم إلغاء الطلب بسبب نقص القطع'],
            ['صيانة مكيفات الهواء', 'مكيفات الهواء لا تعمل بكفاءة وتحتاج صيانة دورية', 'other', 'medium', 'pending', null],
            ['تركيب بلاط في الساحة', 'أرضية الساحة الخارجية بحاجة لتركيب بلاط جديد', 'carpentry', 'medium', 'in_progress', null],
            ['صيانة المئذنة', 'المئذنة بحاجة إلى صيانة وترميم للحماية من التشققات', 'other', 'high', 'pending', null],
            ['طلاء جدران المصلى', 'جدران المصلى بحاجة إلى طلاء وتجديد', 'cleaning', 'low', 'completed', 'تم الطلاء بواسطة فريق من المتطوعين'],
            ['إصلاح شبكة الصرف الصحي', 'انسداد في شبكة الصرف الصحي للمسجد', 'plumbing', 'urgent', 'in_progress', 'يجب التدخل فوراً'],
        ];

        $mosques = Mosque::all();
        $counter = 0;

        foreach ($mosques as $mosque) {
            // كل مسجد يحصل على طلبين صيانة من القوالب بشكل ثابت (كي لا تتكرر عند إعادة البذر)
            $first = $templates[$mosque->id % count($templates)];
            $second = $templates[($mosque->id + 3) % count($templates)];

            foreach ([$first, $second] as [$title, $description, $category, $priority, $status, $notes]) {
                $counter++;;
                $number = 'MNT-' . str_pad((string) $counter, 5, '0', STR_PAD_LEFT);

                $completedAt = $status === 'completed'
                    ? now()->subDays($counter % 20 + 1)
                    : null;

                $scheduledAt = in_array($status, ['in_progress', 'completed'])
                    ? now()->subDays($counter % 10 + 1)
                    : null;

                Maintenance::updateOrCreate(
                    ['maintenance_number' => $number],
                    [
                        'mosque_id' => $mosque->id,
                        'title' => $title,
                        'description' => $description,
                        'category' => $category,
                        'priority' => $priority,
                        'status' => $status,
                        'requested_by' => $mosque->manager_id ?? $requester->id,
                        'scheduled_at' => $scheduledAt,
                        'completed_at' => $completedAt,
                        'notes' => $notes,
                    ]
                );
            }
        }

        $this->command->info('تم إنشاء/تحديث ' . ($counter) . ' طلب صيانة لمساجد دمشق.');
    }
}
