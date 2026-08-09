<?php

namespace Modules\User\Actions;

use Modules\User\Models\User;
use Modules\User\Notifications\SendOTPNotification;

class UpdateProfileAction
{
    public function execute(User $user, array $data): array
    {
        $emailChanged = false;

        // إذا أُرسل بريد جديد يختلف عن البريد الحالي
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $user->pending_email = $data['email'];
            $otp = $user->generateOtp(); // توليد الـ OTP للـ User

            // إرسال الإشعار بالـ OTP بنفس النمط المتبع في النظام
            $user->notify(new SendOTPNotification($otp, 'email_change'));

            $emailChanged = true;
            unset($data['email']); // عدم تحديث البريد الأساسي مباشرةً
        }

        // معالجة تحديث الأسماء بشكل سليم
        $firstName = $data['first_name'] ?? $user->first_name;
        $lastName  = $data['last_name']  ?? $user->last_name;
        $fullName  = $data['name']       ?? trim("{$firstName} {$lastName}");

        $user->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'name'       => $fullName,
            'phone'      => $data['phone'] ?? $user->phone,
        ]);

        return [
            'user'          => $user->fresh(),
            'email_changed' => $emailChanged,
        ];
    }
}
