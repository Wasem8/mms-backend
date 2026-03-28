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

            // 1. Seed Permissions
            $permissions = [
                ['name' => 'manage_users', 'description' => 'Create, edit and delete users'],
                ['name' => 'manage_hlaqat', 'description' => 'Manage Quran circles'],
                ['name' => 'record_attendance', 'description' => 'Mark daily attendance'],
                ['name' => 'view_reports', 'description' => 'View educational reports'],
                ['name' => 'approve_registration', 'description' => 'Approve new student accounts'],
            ];

            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm['name']], $perm);
            }

            // 2. Seed Roles and Link to Permissions
            $roles = [
                'area_manager'   => ['manage_users', 'view_reports', 'approve_registration'],
                'mosque_manager' => ['manage_hlaqat', 'view_reports', 'approve_registration'],
                'hlaqat_supervisor'  => ['manage_hlaqat', 'record_attendance', 'view_reports'],
                'teacher'        => ['record_attendance', 'view_reports'],
                'student'        => [], // Students usually have no special permissions
            ];

            foreach ($roles as $roleName => $perms) {
                $role = Role::firstOrCreate(
                    ['name' => $roleName],
                    ['description' => ucfirst(str_replace('_', ' ', $roleName)) . ' account']
                );

                // Attach permissions using the custom 'role_permissions' table
                $permissionIds = Permission::whereIn('name', $perms)->pluck('id');
                $role->permissions()->sync($permissionIds);
            }

            // 3. Create Demo Users
            $this->createDemoUser('Admin User', 'wasem8115@gmail.com', 'area_manager');
            $this->createDemoUser('Teacher User', 'teacher@tahabeer.com', 'teacher');
            $this->createDemoUser('Halaqat Supervisor', 'supervisor@tahabeer.com', 'hlaqat_supervisor');
            $this->createDemoUser('Student User', 'student@tahabeer.com', 'student');

        });
    }

    // Inside AuthorizationSeeder.php

    private function createDemoUser($name, $email, $roleName)
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('name', $roleName)->first();
        if ($role) {
            // This will now insert into 'user_roles' table
            $user->roles()->sync([$role->id]);
        }
    }
}
