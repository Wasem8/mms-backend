<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Models\User;

class VerifyOtpAction
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
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->clearOtp();

        return $user;
    }
}
