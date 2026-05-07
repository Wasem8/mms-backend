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
        return [
            'email' => 'required|email',
            'role' => 'required|string|exists:roles,name',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
