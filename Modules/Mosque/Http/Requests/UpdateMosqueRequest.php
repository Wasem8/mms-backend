<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateMosqueRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isPrivileged = $this->user()->hasRole('SUPER_ADMIN')
            || $this->user()->hasRole('REGIONAL_ADMIN');

        $rules = [
            'name'           => 'sometimes|string|max:255',
            'image'          => 'sometimes|image|max:5120',
            'working_hours'  => 'sometimes|array',
            'imam'           => 'sometimes|nullable|string|max:255',
            'khatib'         => 'sometimes|nullable|string|max:255',
        ];

        if ($isPrivileged) {
            $rules += [
                'status'      => 'sometimes|string',
                'is_featured' => 'sometimes|boolean',
                'city_id'     => 'sometimes|exists:cities,id',
                'district_id' => 'sometimes|exists:districts,id',
                'manager_id'  => 'sometimes|exists:users,id',
            ];
        } else {
            $rules += [
                'status'          => 'prohibited',
                'is_featured'     => 'prohibited',
                'city_id'         => 'prohibited',
                'district_id'     => 'prohibited',
                'manager_id'      => 'prohibited',
                'average_rating'  => 'prohibited',
                'reviews_count'   => 'prohibited',
                'donation_total'  => 'prohibited',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            '*.prohibited' => 'هذا الحقل خاص بالإدارة العليا فقط.',
        ];
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('mosque'));
    }
}
