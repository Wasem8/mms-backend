<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\Role;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $this->createUser(
            name: 'مدير النظام',
            email: 'admin@test.com',
            role: 'super_admin',
            mosqueId: null,
            roles: $roles
        );

        /*
        |--------------------------------------------------------------------------
        | Mosque Managers
        |--------------------------------------------------------------------------
        |
        | كل مسجد له مدير محدد وثابت.
        | إعادة تشغيل Seeder لن تغير المدير.
        |
        */

        $mosqueManagers = [
            'الجامع الأموي' => [
                'name' => 'أحمد محمد الحسن',
                'email' => 'manager.umayyad@test.com',
            ],

            'جامع الحسن' => [
                'name' => 'محمد خالد العلي',
                'email' => 'manager.hassan@test.com',
            ],

            'جامع الرحمن' => [
                'name' => 'عبد الرحمن محمود',
                'email' => 'manager.rahman@test.com',
            ],

            'جامع الإيمان' => [
                'name' => 'عمر أحمد الخطيب',
                'email' => 'manager.iman@test.com',
            ],

            'جامع عثمان بن عفان' => [
                'name' => 'خالد عبد الله',
                'email' => 'manager.uthman@test.com',
            ],

            'جامع التوبة' => [
                'name' => 'يوسف محمد',
                'email' => 'manager.tawba@test.com',
            ],

            'جامع النور' => [
                'name' => 'عبد الله حسن',
                'email' => 'manager.noor@test.com',
            ],

            'جامع الرحمة' => [
                'name' => 'إبراهيم أحمد',
                'email' => 'manager.rahma@test.com',
            ],

            'جامع خالد بن الوليد' => [
                'name' => 'مصطفى علي',
                'email' => 'manager.khalid@test.com',
            ],

            'جامع التقوى' => [
                'name' => 'حسن محمود',
                'email' => 'manager.taqwa@test.com',
            ],
        ];

        foreach ($mosqueManagers as $mosqueName => $managerData) {

            $mosque = Mosque::where('name', $mosqueName)
                ->where('city', 'دمشق')
                ->first();

            if (!$mosque) {
                $this->command->warn(
                    "⚠️ لم يتم العثور على المسجد: {$mosqueName}"
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | حماية مهمة:
            | إذا كان للمسجد مدير بالفعل لا نغيره.
            |--------------------------------------------------------------------------
            */

            if ($mosque->manager_id) {

                $existingManager = User::find($mosque->manager_id);

                if ($existingManager) {

                    $this->command->info(
                        "ℹ️ {$mosque->name} لديه مدير بالفعل: {$existingManager->email}"
                    );

                    continue;
                }

                /*
                | manager_id يشير إلى مستخدم محذوف.
                | في هذه الحالة نعيد تعيينه للمدير الصحيح.
                */

                $mosque->update([
                    'manager_id' => null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | إنشاء المدير
            |--------------------------------------------------------------------------
            */

            $manager = $this->createUser(
                name: $managerData['name'],
                email: $managerData['email'],
                role: 'mosque_manager',
                mosqueId: $mosque->id,
                roles: $roles
            );

            /*
            |--------------------------------------------------------------------------
            | ربط المدير بالمسجد
            |--------------------------------------------------------------------------
            */

            $mosque->update([
                'manager_id' => $manager->id,
            ]);

            $this->command->info(
                "✅ تم تعيين {$manager->name} مديراً لـ {$mosque->name}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Supervisor
        |--------------------------------------------------------------------------
        */

        $this->createUser(
            name: 'مشرف الحلقات',
            email: 'supervisor@test.com',
            role: 'halaqa_supervisor',
            mosqueId: Mosque::first()?->id,
            roles: $roles
        );

        /*
        |--------------------------------------------------------------------------
        | Teacher
        |--------------------------------------------------------------------------
        */

        $this->createUser(
            name: 'الشيخ عبد الرحمن',
            email: 'teacher@test.com',
            role: 'teacher',
            mosqueId: Mosque::first()?->id,
            roles: $roles
        );

        /*
        |--------------------------------------------------------------------------
        | Parent
        |--------------------------------------------------------------------------
        */

        $this->createUser(
            name: 'ولي الأمر الرئيسي',
            email: 'parent@test.com',
            role: 'parent',
            mosqueId: null,
            roles: $roles
        );

        /*
        |--------------------------------------------------------------------------
        | Volunteer
        |--------------------------------------------------------------------------
        */

        $this->createUser(
            name: 'المتطوع الرئيسي',
            email: 'volunteer@test.com',
            role: 'volunteer',
            mosqueId: Mosque::first()?->id,
            roles: $roles
        );

        $this->command->info('✅ تم إنشاء جميع المستخدمين والأدوار بنجاح.');
    }

    /**
     * إنشاء مستخدم وربطه بدور واحد.
     */
    private function createUser(
        string $name,
        string $email,
        string $role,
        ?int $mosqueId,
               $roles
    ): User {

        $user = User::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'mosque_id' => $mosqueId,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | التأكد من وجود الدور
        |--------------------------------------------------------------------------
        */

        if (!isset($roles[$role])) {
            throw new \RuntimeException(
                "Role [{$role}] غير موجود. شغل RoleSeeder أولاً."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | عدم تكرار الدور
        |--------------------------------------------------------------------------
        */

        $user->roles()->syncWithoutDetaching([
            $roles[$role],
        ]);

        return $user;
    }
}
