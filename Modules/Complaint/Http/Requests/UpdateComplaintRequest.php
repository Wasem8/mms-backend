<?php

namespace Modules\Complaint\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,in_progress,resolved,canceled',
            'note' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => __('messages.complaint.validation.status.required'),
            'status.in' => __('messages.complaint.validation.status.in'),
            'note.string' => __('messages.complaint.validation.note.string'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
