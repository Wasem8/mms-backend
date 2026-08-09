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
            'status' => 'required|string|in:in_progress,completed,cancelled',
            'notes'  => 'nullable|string|required_if:status,cancelled',
        ];
    }
    public function messages(): array
    {
        return [
            'notes.required_if' => __('messages.maintenance.notes_required_when_cancelling'),
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
