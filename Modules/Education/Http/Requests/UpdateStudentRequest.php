<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'    => 'sometimes|required|string|max:255',
            'last_name'     => 'sometimes|required|string|max:255',
            'date_of_birth' => 'sometimes|required|date|before:today',
            'gender'        => 'sometimes|required|in:male,female',
            'status'        => 'sometimes|required|in:active,inactive,pending',
            'mosque_id'     => 'sometimes|required|exists:mosques,id',
            'parent_id'     => 'sometimes|required|exists:users,id',
        ];
    }
}
