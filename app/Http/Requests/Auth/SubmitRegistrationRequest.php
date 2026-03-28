<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:registration_requests|unique:users',
            'password'     => 'required|min:8|confirmed',
            'age'          => 'required|integer|min:5|max:25',
            'grade'        => 'required|string',
            'parent_phone' => 'required|string',
            'address'      => 'nullable|string',
            'current_hifz' => 'nullable|string',
        ];
    }
}
