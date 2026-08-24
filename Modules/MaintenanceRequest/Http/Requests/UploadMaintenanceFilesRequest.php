<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMaintenanceFilesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'files'   => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => __('messages.maintenance.files_required'),
            'files.min'      => __('messages.maintenance.files_min'),
            'files.*.mimes'  => __('messages.maintenance.files_mimes'),
            'files.*.max'    => __('messages.maintenance.files_max'),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('mosque_manager');
    }
}
