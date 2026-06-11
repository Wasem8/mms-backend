<?php

namespace Modules\Donation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Donation\Models\Campaign;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            [
                'mosque_id' => 1,
                'title' => 'ترميم المسجد',
                'description' => 'حملة لجمع التبرعات لترميم المسجد وإعادة تأهيله',
                'target_amount' => 500000,
                'collected_amount' => 350000,
                'status' => 'active',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'priority' => 'high',
            ],
            [
                'mosque_id' => 1,
                'title' => 'شراء فرش جديد',
                'description' => 'حملة لشراء فرش جديد للمسجد',
                'target_amount' => 100000,
                'collected_amount' => 100000,
                'status' => 'completed',
                'start_date' => '2024-06-01',
                'end_date' => '2024-09-01',
                'priority' => 'medium',
            ],
            [
                'mosque_id' => 2,
                'title' => 'تركيب مكيفات',
                'description' => 'حملة لتركيب مكيفات في قاعة الصلاة',
                'target_amount' => 200000,
                'collected_amount' => 50000,
                'status' => 'active',
                'start_date' => '2025-03-01',
                'end_date' => null,
                'priority' => 'high',
            ],
            [
                'mosque_id' => 3,
                'title' => 'توسعة المصلى',
                'description' => 'حملة لتوسعة المصلى لاستيعاب المزيد من المصلين',
                'target_amount' => 1000000,
                'collected_amount' => 0,
                'status' => 'paused',
                'start_date' => '2025-05-01',
                'end_date' => '2026-05-01',
                'priority' => 'medium',
            ],
            [
                'mosque_id' => 4,
                'title' => 'صيانة دورية',
                'description' => 'حملة لصيانة المرافق العامة للمسجد',
                'target_amount' => 50000,
                'collected_amount' => 50000,
                'status' => 'completed',
                'start_date' => '2024-10-01',
                'end_date' => '2024-12-31',
                'priority' => 'low',
            ],
            [
                'mosque_id' => 5,
                'title' => 'مشروع المياه',
                'description' => 'حملة لحفر بئر ماء وتركيب نظام تنقية',
                'target_amount' => 150000,
                'collected_amount' => 75000,
                'status' => 'active',
                'start_date' => '2025-02-01',
                'end_date' => '2025-08-01',
                'priority' => 'high',
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::create($campaign);
        }
    }
}
