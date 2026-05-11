<?php

namespace Modules\User\Actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\User\Models\User;

class ResetPasswordAction
{
    public function execute(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->verifyOtp($data['otp'])) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP.'
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        $user->clearOtp();

        return true;
    }


}
