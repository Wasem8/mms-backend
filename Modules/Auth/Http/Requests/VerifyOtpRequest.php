<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp'   => ['required', 'string', 'size:6'], // تأكد أن الطول مطابق لـ OTP_LENGTH في الـ Service
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'otp.required'   => 'يرجى إدخال رمز التحقق.',
            'otp.size'       => 'يجب أن يتكون رمز التحقق من 6 أرقام.',
        ];
    }
}
