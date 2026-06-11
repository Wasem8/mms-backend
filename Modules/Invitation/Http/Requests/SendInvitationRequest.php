<?php

namespace Modules\Invitation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendInvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user();
        return [
            'email' => 'required|email',
            'role' => 'required|string|exists:roles,name',
            'mosque_id' => ($user && $user->mosque_id) ? 'nullable|exists:mosques,id' : 'required|exists:mosques,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'mosque_id.required' => 'يرجى تحديد المسجد المراد إرسال الدعوة إليه.',
            'mosque_id.exists'   => 'المسجد المحدد غير موجود في النظام.',
        ];
    }
}
