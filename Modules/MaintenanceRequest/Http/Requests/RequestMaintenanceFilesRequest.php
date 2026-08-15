<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestMaintenanceFilesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required' => __('messages.maintenance.files_request_note_required'),
            'note.max'      => __('messages.maintenance.files_request_note_max'),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super_admin');
    }
}
