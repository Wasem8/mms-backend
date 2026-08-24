<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'المدير العام',
            ],
            [
                'name' => 'mosque_manager',
                'display_name' => 'مدير المسجد',
            ],
            [
                'name' => 'halaqa_supervisor',
                'display_name' => 'المشرف التربوي',
            ],
            [
                'name' => 'teacher',
                'display_name' => 'المعلم',
            ],
            [
                'name' => 'parent',
                'display_name' => 'ولي الأمر',
            ],
            [
                'name' => 'volunteer',
                'display_name' => 'المتطوع',
            ],
            [
                'name' => 'guest',
                'display_name' => 'زائر',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                [
                    'display_name' => $role['display_name'],
                ]
            );
        }
    }
}
