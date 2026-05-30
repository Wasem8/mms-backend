<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessMaintenanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:in_progress,completed,cancelled'],
            'notes'  => ['nullable', 'string', 'required_if:status,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required_if' => 'A reason is required when cancelling a request.',
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
