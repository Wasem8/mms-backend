<?php

namespace Modules\Complaint\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Complaint\Models\Complaint;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $complaints = [
            [
                'complaint_number' => 'CMP-00001',
                'title' => 'انقطاع التيار الكهربائي',
                'description' => 'التيار الكهربائي ينقطع بشكل متكرر خلال أوقات الصلاة',
                'mosque_id' => 1,
                'user_id' => 5,
                'status' => 'pending',
                'priority' => 'medium',
                'complaint_type' => 'power_outage',
                'email' => null,
                'is_anonymous' => false,
            ],
            [
                'complaint_number' => 'CMP-00002',
                'title' => 'نقص خدمات النظافة',
                'description' => 'دورات المياه بحاجة إلى تنظيف مستمر',
                'mosque_id' => 1,
                'user_id' => null,
                'status' => 'in_progress',
                'priority' => 'high',
                'complaint_type' => 'service_missing',
                'email' => null,
                'is_anonymous' => true,
            ],
            [
                'complaint_number' => 'CMP-00003',
                'title' => 'سوء تعامل موظف',
                'description' => 'أحد الموظفين يتعامل بطريقة غير لائقة مع المصلين',
                'mosque_id' => 2,
                'user_id' => 5,
                'status' => 'resolved',
                'priority' => 'high',
                'complaint_type' => 'employee_misconduct',
                'email' => 'user@example.com',
                'is_anonymous' => false,
            ],
            [
                'complaint_number' => 'CMP-00004',
                'title' => 'خلل في نظام الصوت',
                'description' => 'مكبرات الصوت في المصلى تعمل بشكل متقطع',
                'mosque_id' => 3,
                'user_id' => 4,
                'status' => 'pending',
                'priority' => 'low',
                'complaint_type' => 'technical_issue',
                'email' => null,
                'is_anonymous' => false,
            ],
            [
                'complaint_number' => 'CMP-00005',
                'title' => 'شبهة فساد مالي',
                'description' => 'تبلغ عن وجود تلاعب في حسابات التبرعات',
                'mosque_id' => 4,
                'user_id' => null,
                'status' => 'canceled',
                'priority' => 'medium',
                'complaint_type' => 'corruption',
                'email' => 'whistleblower@test.com',
                'is_anonymous' => true,
            ],
            [
                'complaint_number' => 'CMP-00006',
                'title' => 'نقص سجاد للصلاة',
                'description' => 'السجاد في قسم النساء بحاجة إلى استبدال',
                'mosque_id' => 5,
                'user_id' => 5,
                'status' => 'in_progress',
                'priority' => 'medium',
                'complaint_type' => 'service_missing',
                'email' => null,
                'is_anonymous' => false,
            ],
        ];

        foreach ($complaints as $complaint) {
            Complaint::create($complaint);
        }
    }
}
