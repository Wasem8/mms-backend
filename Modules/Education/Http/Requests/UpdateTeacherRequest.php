<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'           => 'sometimes|required|string|max:255',
            'phone'          => 'sometimes|required|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'status'         => 'sometimes|required|in:active,paused,suspended',
            'notes'          => 'nullable|string|max:1000',
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
