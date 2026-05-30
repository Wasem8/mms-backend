<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\MaintenanceRequest\Enums\MaintenanceCategory;
use Modules\MaintenanceRequest\Enums\MaintenancePriority;

class UpdateMaintenanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'max:255'],
            'description'  => ['sometimes', 'string', 'max:5000'],
            'category'     => ['sometimes', 'string', 'in:electrical,plumbing,carpentry,cleaning,other'],
            'priority'     => ['sometimes', 'string', 'in:low,medium,high,urgent'],
            'assigned_to'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'scheduled_at' => ['sometimes', 'nullable', 'date'],
            'notes'        => ['sometimes', 'nullable', 'string'],
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
