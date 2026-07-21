<?php

namespace Modules\User\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\Role;
use Modules\User\Models\User;
use Modules\User\Notifications\SendOTPNotification;

class RegisterParentAction
{
    public function execute(array $data)
    {
        $user = DB::transaction(function () use ($data) {

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'status'   => 'inactive',
            ]);

            $role = Role::firstWhere('name', 'parent');

            $user->roles()->attach($role->id);

            $otp = $user->generateOtp();

            return [
                'user' => $user,
                'otp'  => $otp,
            ];
        });

        $user['user']->notify(
            new SendOTPNotification(
                $user['otp'],
                'verification'
            )
        );

        return $user['user'];
    }
}
