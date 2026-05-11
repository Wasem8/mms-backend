<?php

namespace Modules\User\Actions;

use Modules\User\Models\User;
use Modules\User\Notifications\SendOTPNotification;

class ForgotPasswordAction
{
    public function execute(string $email)
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            // لا تكشف هل الإيميل موجود
            return true;
        }

        $otp = $user->generateOtp();

        $user->notify(new SendOTPNotification($otp, 'reset'));

        return true;
    }
}
