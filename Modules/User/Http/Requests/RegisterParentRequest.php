<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterParentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required_without:name|nullable|string|max:100',
            'last_name'  => 'required_without:name|nullable|string|max:100',
            'name'       => 'required_without:first_name|nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone'      => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
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
