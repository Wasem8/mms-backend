<?php

namespace Modules\Complaint\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitComplaintRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'mosque_id' => 'required|exists:mosques,id',
            'complaint_type' => 'required|in:service_missing,power_outage,corruption,employee_misconduct,technical_issue',
            'priority' => 'nullable|in:low,medium,high',
            'email' => 'nullable|email',
            'status' => 'nullable|in:pending,in_progress,resolved,canceled',
            'is_anonymous' => 'in:0,1,true,false,"0","1"',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
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
