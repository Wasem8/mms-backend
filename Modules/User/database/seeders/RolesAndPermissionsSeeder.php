<?php


namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\Permission;
use Modules\User\Models\Role;
use Modules\User\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {

        /*
        |--------------------------------------------------
        | ROLES
        |--------------------------------------------------
        */

        $roles = [
            'super_admin',
            'mosque_manager',
            'halaqa_supervisor',
            'teacher',
            'parent',
            'guest'
        ];

        foreach ($roles as $role) {
            Role::create([
                'name' => $role,
                'display_name' => ucfirst(str_replace('_', ' ', $role))
            ]);
        }

        /*
        |--------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------
        */

        $permissions = [
            'manage_users',

            // 🔥 INVITATIONS
            'invite_mosque_manager',
            'invite_halaqa_supervisor',
            'invite_teacher',

            'view_mosques',
            'create_mosque',
            'update_mosque',
            'delete_mosque',

            'manage_donations',
            'manage_needs',
            'manage_maintenance',
            'manage_programs',

            'create_sermon',
            'approve_sermon',

            'manage_halaqat',
            'manage_students',

            'record_attendance',
            'evaluate_students',

            'send_complaints',
            'view_notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'display_name' => ucfirst(str_replace('_', ' ', $permission))
            ]);
        }

        /*
        |--------------------------------------------------
        | ASSIGN PERMISSIONS TO ROLES
        |--------------------------------------------------
        */

        $superAdmin = Role::where('name', 'super_admin')->first();
        $mosqueManager = Role::where('name', 'mosque_manager')->first();
        $supervisor = Role::where('name', 'halaqa_supervisor')->first();
        $teacher = Role::where('name', 'teacher')->first();
        $parent = Role::where('name', 'parent')->first();

        $superAdmin->permissions()->sync(Permission::all());

        $mosqueManager->permissions()->sync(
            Permission::whereIn('name', [
                'invite_halaqa_supervisor',
                'view_mosques',
                'create_mosque',
                'update_mosque',
                'manage_programs',
            ])->pluck('id')
        );

        $supervisor->permissions()->sync(
            Permission::whereIn('name', [
                'invite_teacher',
                'manage_halaqat',
                'manage_students',
            ])->pluck('id')
        );

        $teacher->permissions()->sync(
            Permission::whereIn('name', [
                'record_attendance',
                'evaluate_students'
            ])->pluck('id')
        );

        $parent->permissions()->sync(
            Permission::whereIn('name', [
                'view_notifications',
                'send_complaints'
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------
        | USERS SEED
        |--------------------------------------------------
        */
        $mosque = Mosque::first();

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@test.com',
                'password' => 'password',
                'role' => 'super_admin',
                'mosque_id' => null,
            ],
            [
                'name' => 'Mosque Manager',
                'email' => 'manager@test.com',
                'password' => 'password',
                'role' => 'mosque_manager',
                'mosque_id' => $mosque?->id,
            ],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@test.com',
                'password' => 'password',
                'role' => 'halaqa_supervisor',
                'mosque_id' => $mosque?->id,
            ],
            [
                'name' => 'Teacher',
                'email' => 'teacher@test.com',
                'password' => 'password',
                'role' => 'teacher',
                'mosque_id' => $mosque?->id,
            ],
            [
                'name' => 'Parent',
                'email' => 'parent@test.com',
                'password' => 'password',
                'role' => 'parent',
                'mosque_id' => null,
            ],
        ];

        foreach ($users as $data) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
                'status' => 'active',
                'mosque_id' => $data['mosque_id'],
            ]);

            $role = Role::where('name', $data['role'])->first();

            // attach role (pivot role_user)
            $user->roles()->attach($role->id);
        }
    }
}
