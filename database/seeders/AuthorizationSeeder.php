<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // --- 1. إنشاء الصلاحيات (Permissions) ---
            $permissions = [
                'manage_users'    => 'إدارة المستخدمين والدعوات',
                'manage_mosques'  => 'إدارة المساجد والمناطق',
                'manage_hlaqat'   => 'إدارة الحلقات والطلاب',
                'record_marks'    => 'تسجيل درجات الطلاب',
                'view_reports'    => 'عرض التقارير والإحصائيات',
                'send_notif'      => 'إرسال الإشعارات والتعميمات',
            ];

            foreach ($permissions as $slug => $desc) {
                Permission::firstOrCreate(
                    ['name' => $slug],
                    ['description' => $desc]
                );
            }

            // --- 2. إنشاء الأدوار وربطها بالصلاحيات (Roles) ---
            $rolesConfig = [
                'region_manager' => [
                    'manage_users', 'manage_mosques', 'view_reports', 'send_notif'
                ],
                'mosque_manager' => [
                    'manage_hlaqat', 'view_reports', 'manage_users'
                ],
                'supervisor' => [
                    'manage_hlaqat', 'record_marks', 'view_reports'
                ],
                'teacher' => [
                    'record_marks', 'view_reports'
                ],
                'parent' => [
                    'view_reports'
                ],
            ];

            foreach ($rolesConfig as $roleSlug => $rolePerms) {
                $role = Role::firstOrCreate(
                    ['name' => $roleSlug],
                    ['description' => ucwords(str_replace('_', ' ', $roleSlug))]
                );

                // جلب IDs الصلاحيات وربطها بالدور
                $permIds = Permission::whereIn('name', $rolePerms)->pluck('id');
                $role->permissions()->sync($permIds);
            }

            // --- 3. إنشاء مستخدمين تجريبيين (Demo Users) ---

            // مدير المنطقة (Active)
            $this->createAdminUser('وسيم البقاعي', 'wasem8115@gmail.com', 'region_manager');

            // مدير مسجد (Active)
            $this->createAdminUser('مدير المسجد', 'mosque_admin@wasl.com', 'mosque_manager');

            // معلم (Inactive - كمثال لحساب ينتظر التفعيل حسب الـ SRS)
            $this->createAdminUser('الأستاذ أحمد', 'teacher@wasl.com', 'teacher', false);

        });
    }

    /**
     * دالة مساعدة لإنشاء المستخدم وربط دوره وتحديد حالته
     */
    private function createAdminUser($name, $email, $roleSlug, $isActive = true)
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'      => $name,
                'password'  => Hash::make('password123'), // كلمة مرور افتراضية
                'is_active' => $isActive,
                'email_verified_at' => now(),
            ]
        );

        // ربط العلاقة في جدول user_role
        $role = Role::where('name', $roleSlug)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        return $user;
    }
}
