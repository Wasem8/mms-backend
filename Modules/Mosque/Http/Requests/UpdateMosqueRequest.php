<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateMosqueRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('is_featured')) {
            $this->merge([
                'is_featured' => filter_var($this->input('is_featured'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        $isPrivileged = $this->user()->hasRole('super_admin');

        $rules = [
            'name'           => 'sometimes|string|max:255',
            'image'          => 'sometimes|image|max:5120',
            'working_hours' => 'nullable', 'string', 'max:500',
            'imam'           => 'sometimes|nullable|string|max:255',
            'khatib'         => 'sometimes|nullable|string|max:255',
        ];

        if ($isPrivileged) {
            $rules += [
                'status'      => 'sometimes|string',
                'is_featured' => 'sometimes|boolean',
                'city_id'     => 'sometimes|exists:cities,id',
                'district_id' => 'sometimes|exists:districts,id',
                'manager_id'  => [
                    'sometimes',
                    'exists:users,id',
                    Rule::unique('mosques', 'manager_id')
                        ->ignore($this->route('mosque')),
                ],
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
            '*.prohibited'      => __('messages.mosque.privileged_field'),
            'manager_id.unique' => __('messages.mosque.manager_already_assigned'),
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('mosque'));
    }
}
