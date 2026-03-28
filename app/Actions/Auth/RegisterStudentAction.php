<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterStudentAction {
    public function execute(array $data): User {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $role = Role::where('name', 'student')->first();
            $user->roles()->attach($role);

            return $user;
        });
    }
}
