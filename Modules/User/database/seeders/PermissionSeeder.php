<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // =========================
            // USERS
            // =========================
            [
                'name' => 'manage_users',
                'display_name' => 'إدارة المستخدمين',
            ],

            // =========================
            // INVITATIONS
            // =========================
            [
                'name' => 'invite_mosque_manager',
                'display_name' => 'دعوة مدير مسجد',
            ],
            [
                'name' => 'invite_halaqa_supervisor',
                'display_name' => 'دعوة مشرف تربوي',
            ],
            [
                'name' => 'invite_teacher',
                'display_name' => 'دعوة معلم',
            ],

            // =========================
            // MOSQUES
            // =========================
            [
                'name' => 'view_mosques',
                'display_name' => 'عرض المساجد',
            ],
            [
                'name' => 'create_mosque',
                'display_name' => 'إنشاء مسجد',
            ],
            [
                'name' => 'update_mosque',
                'display_name' => 'تعديل مسجد',
            ],
            [
                'name' => 'delete_mosque',
                'display_name' => 'حذف مسجد',
            ],

            // =========================
            // DONATIONS
            // =========================
            [
                'name' => 'manage_donations',
                'display_name' => 'إدارة التبرعات',
            ],

            // =========================
            // NEEDS
            // =========================
            [
                'name' => 'manage_needs',
                'display_name' => 'إدارة احتياجات المسجد',
            ],

            // =========================
            // MAINTENANCE
            // =========================
            [
                'name' => 'manage_maintenance',
                'display_name' => 'إدارة طلبات الصيانة',
            ],

            // =========================
            // PROGRAMS
            // =========================
            [
                'name' => 'manage_programs',
                'display_name' => 'إدارة البرامج',
            ],

            // =========================
            // SERMONS
            // =========================
            [
                'name' => 'create_sermon',
                'display_name' => 'إنشاء خطبة',
            ],
            [
                'name' => 'approve_sermon',
                'display_name' => 'اعتماد الخطبة',
            ],

            // =========================
            // HALAQAT
            // =========================
            [
                'name' => 'manage_halaqat',
                'display_name' => 'إدارة الحلقات',
            ],

            // =========================
            // STUDENTS
            // =========================
            [
                'name' => 'manage_students',
                'display_name' => 'إدارة الطلاب',
            ],

            // =========================
            // ATTENDANCE
            // =========================
            [
                'name' => 'record_attendance',
                'display_name' => 'تسجيل الحضور',
            ],

            // =========================
            // EVALUATIONS
            // =========================
            [
                'name' => 'evaluate_students',
                'display_name' => 'تقييم الطلاب',
            ],

            // =========================
            // COMPLAINTS
            // =========================
            [
                'name' => 'send_complaints',
                'display_name' => 'إرسال الشكاوى',
            ],

            // =========================
            // NOTIFICATIONS
            // =========================
            [
                'name' => 'view_notifications',
                'display_name' => 'عرض الإشعارات',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                ]
            );
        }
    }
}
