<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\Permission;
use Modules\User\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = Permission::pluck('id', 'name');

        $rolePermissions = [

            // ==========================================
            // SUPER ADMIN
            // ==========================================
            'super_admin' => [
                'manage_users',

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
            ],

            // ==========================================
            // MOSQUE MANAGER
            // ==========================================
            'mosque_manager' => [
                'invite_halaqa_supervisor',
                'invite_teacher',

                'view_mosques',
                'update_mosque',

                'manage_donations',
                'manage_needs',
                'manage_maintenance',
                'manage_programs',

                'create_sermon',
                'approve_sermon',

                'manage_halaqat',
                'manage_students',

                'view_notifications',
            ],

            // ==========================================
            // HALAQA SUPERVISOR
            // ==========================================
            'halaqa_supervisor' => [
                'invite_teacher',

                'manage_halaqat',
                'manage_students',

                'record_attendance',
                'evaluate_students',

                'view_notifications',
            ],

            // ==========================================
            // TEACHER
            // ==========================================
            'teacher' => [
                'record_attendance',
                'evaluate_students',
                'view_notifications',
            ],

            // ==========================================
            // PARENT
            // ==========================================
            'parent' => [
                'send_complaints',
                'view_notifications',
            ],

            // ==========================================
            // VOLUNTEER
            // ==========================================
            'volunteer' => [
                'view_mosques',
                'manage_maintenance',
                'send_complaints',
                'view_notifications',
            ],

            // ==========================================
            // GUEST
            // ==========================================
            'guest' => [
                'view_mosques',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {

            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                continue;
            }

            $permissionIds = collect($permissionNames)
                ->map(fn ($name) => $permissions[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
