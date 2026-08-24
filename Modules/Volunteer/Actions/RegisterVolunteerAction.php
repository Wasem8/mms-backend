<?php

namespace Modules\Volunteer\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\User\Models\Role;
use Modules\User\Models\User;

class RegisterVolunteerAction
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'email_verified_at' => now(),
            'mosque_id' => $data['mosque_id'],
        ]);

        $role = Role::where('name', 'volunteer')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        return $user->load('roles.permissions');
    }
}
