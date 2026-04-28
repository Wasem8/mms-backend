<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMosqueRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'working_hours' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'maintenance', 'closed'])],
            'is_featured' => ['boolean'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'decimal:0,8', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'decimal:0,8', 'between:-180,180'],
            'imam' => ['nullable', 'string'],
            'khatib' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['required', 'integer', 'exists:facilities,id'],
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
