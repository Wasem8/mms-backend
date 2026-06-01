<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMaintenanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mosque_id'    => ['required', 'integer', 'exists:mosques,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string', 'max:5000'],
            'category'     => ['required', 'string', 'in:electrical,plumbing,carpentry,cleaning, other'],
            'priority'     => ['sometimes', 'string', 'in:low,medium,high, urgent'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'notes'        => ['nullable', 'string'],
            'files'        => ['nullable', 'array', 'max:10'],
            'files.*'      => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
