<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\MaintenanceRequest\Enums\MaintenanceStatus;

class UpdateMaintenanceStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status'     => ['required', new Enum(MaintenanceStatus::class)],
            'changed_by' => ['required', 'string', 'max:255'],
            'notes'      => ['nullable', 'string'],
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
