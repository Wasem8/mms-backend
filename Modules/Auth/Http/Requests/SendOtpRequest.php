<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ملاحظة: لا نستخدم exists هنا إذا أردنا اتباع سياسة الأمان التي تتبعها في الـ Service
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب لإرسال الرمز.',
            'email.email'    => 'يرجى إدخال بريد إلكتروني صحيح.',
        ];
    }
}
