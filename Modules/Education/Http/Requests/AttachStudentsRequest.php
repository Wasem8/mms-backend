<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Models\User;

class AttachStudentsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'students' => 'required|array|min:1',

            'students.*' => [
                'required',
                'distinct',
                'integer',
                'exists:students,id',
            ],
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
