<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'        => 'nullable|string|max:255',
            'description'  => 'nullable|string|max:5000',
            'category'     => 'nullable|string|in:electrical,plumbing,carpentry,cleaning,other',
            'priority'     => 'nullable|string|in:low,medium,high,urgent',
            'scheduled_at' => 'nullable|date',
            'notes'        => 'nullable|string',
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
