<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginWithPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // مسموح للجميع بمحاولة تسجيل الدخول
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'يرجى إدخال البريد الإلكتروني.',
            'email.email'       => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.exists'      => 'بيانات الدخول هذه غير صحيحة.', // رسالة غامضة قليلاً للأمان
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ];
    }
}
