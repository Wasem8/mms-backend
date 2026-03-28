<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\SendOTPNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction {
    public function execute(array $credentials) {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        // Check if student is approved (if applicable)
        // if ($user->hasRole('student') && !$user->is_approved) { ... }


        $otp = $user->generateOtp();

        $user->notify(new SendOTPNotification($otp));

        // Send Email Logic here (e.g., Mail::to($user)->send(new OtpMail($otp)))

        return $user;
    }
}
