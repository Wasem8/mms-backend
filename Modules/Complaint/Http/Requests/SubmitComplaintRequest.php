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

    public function messages(): array
    {
        return [
            'title.required' => __('messages.complaint.validation.title.required'),
            'title.max' => __('messages.complaint.validation.title.max'),
            'description.required' => __('messages.complaint.validation.description.required'),
            'description.min' => __('messages.complaint.validation.description.min'),
            'mosque_id.required' => __('messages.complaint.validation.mosque_id.required'),
            'mosque_id.exists' => __('messages.complaint.validation.mosque_id.exists'),
            'complaint_type.required' => __('messages.complaint.validation.complaint_type.required'),
            'complaint_type.in' => __('messages.complaint.validation.complaint_type.in'),
            'priority.in' => __('messages.complaint.validation.priority.in'),
            'email.email' => __('messages.complaint.validation.email.email'),
            'files.*.mimes' => __('messages.complaint.validation.files.mimes'),
            'files.*.max' => __('messages.complaint.validation.files.max'),
            'status.in' => __('messages.complaint.validation.status.in'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
