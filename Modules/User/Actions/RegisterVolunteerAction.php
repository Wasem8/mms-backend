<?php

namespace Modules\User\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\User\Models\Role;
use Modules\User\Models\User;
use Modules\User\Notifications\SendOTPNotification;

class RegisterVolunteerAction
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'inactive',
        ]);

        $role = Role::where('name', 'volunteer')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        $otp = $user->generateOtp();
        $user->notify(new SendOTPNotification($otp, 'verification'));

        return $user;
    }
}
