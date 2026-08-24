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

            // استخراج وتحديد الأسماء
            $firstName = $data['first_name'] ?? null;
            $lastName  = $data['last_name'] ?? null;

            // إذا أُرسل name نستخدمه، وإلا ندمج first_name و last_name تلقائياً
            $fullName  = $data['name'] ?? trim("{$firstName} {$lastName}");

            $user = User::create([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'name'       => $fullName,
                'email'      => $data['email'],
                'phone'      => $data['phone'] ?? null,
                'password'   => Hash::make($data['password']),
                'status'     => 'inactive',
            ]);

            $role = Role::firstWhere('name', 'parent');

            if ($role) {
                $user->roles()->attach($role->id);
            }

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
