<?php

namespace Modules\Complaint\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'complaint_number' => 'required|unique:complaints',
            'description' => 'required',
            'image' => 'nullable|image',
            'status' => 'required|in:pending,in_progress,resolved,canceled',
            'user_id' => 'required|exists:users,id',
            'mosque_id' => 'required|exists:mosques,id',];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
