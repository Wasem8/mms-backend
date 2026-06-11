<?php

namespace Modules\Invitation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AcceptInvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'name' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 🎯 التحكم في مسار الفشل عند حدوث خطأ في التحقق (مثل كلمة المرور قصيرة)
     */
    protected function failedValidation(Validator $validator)
    {
        // إذا كان الطلب قادماً من المتصفح (وليس طلب API نقي من الجوال)
        if (! $this->wantsJson()) {

            // جلب التوكن لإعادة تمريره في الرابط حتى لا تضيع الصفحة
            $token = $this->input('token');

            // إرجاع المستخدم لصفحة الفورم مع الأخطاء والمدخلات السابقة
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo(route('invitations.accept_form', ['token' => $token]));
        }

        // إذا كان الطلب من تطبيق الجوال يكمل عمله الطبيعي كـ JSON
        parent::failedValidation($validator);
    }
}
