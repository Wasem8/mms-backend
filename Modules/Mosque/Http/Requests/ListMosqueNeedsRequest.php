<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ListMosqueNeedsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'     => ['nullable', 'string', Rule::in(['open', 'partially_fulfilled', 'fulfilled'])],
            'type'       => ['nullable', 'string', Rule::in(['financial', 'maintenance', 'equipment', 'supplies', 'other'])],
            'urgent'     => ['nullable', 'boolean'],
            'city'       => ['nullable', 'string', 'max:100'],
            'mosque_id'  => ['nullable', 'integer', 'exists:mosques,id'],
            'search'     => ['nullable', 'string', 'max:255'],

            // النقطة 3: أي قيمة غير موجودة بهذه القائمة ترجع 422 تلقائيًا
            'sort_by'    => ['nullable', 'string', Rule::in(['urgency', 'funding_gap', 'created_at', 'deadline', 'distance'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],

            // near = "lat,lng"
            'near'       => ['nullable', 'regex:/^-?\d{1,3}(\.\d+)?,-?\d{1,3}(\.\d+)?$/'],
            'radius_km'  => ['nullable', 'numeric', 'min:0', 'max:500'],

            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * تحقق إضافي: sort_by=distance لازم يجي معه near
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('sort_by') === 'distance' && ! $this->filled('near')) {
                $validator->errors()->add(
                    'near',
                    __('messages.mosque.near_required_for_distance_sort')
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'sort_by.in' => __('messages.mosque.invalid_sort_by'),
            'near.regex' => __('messages.mosque.invalid_near_format'),
        ];
    }

    /**
     * تحويل near إلى [lat, lng] بعد التحقق
     */
    public function parsedNear(): ?array
    {
        if (! $this->filled('near')) {
            return null;
        }

        [$lat, $lng] = explode(',', $this->input('near'));
        $lat = (float) $lat;
        $lng = (float) $lng;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [$lat, $lng];
    }

    public function toFilters(): array
    {
        $validated = $this->validated();
        $near = $this->parsedNear();

        return [
            'status'     => $validated['status'] ?? null,
            'type'       => $validated['type'] ?? null,
            'urgent'     => $validated['urgent'] ?? null,
            'city'       => $validated['city'] ?? null,
            'mosque_id'  => $validated['mosque_id'] ?? null,
            'search'     => $validated['search'] ?? null,
            'sort_by'    => $validated['sort_by'] ?? 'created_at',
            'sort_order' => $validated['sort_order'] ?? 'desc',
            'near'       => $near,
            'radius_km'  => $validated['radius_km'] ?? null,
        ];
    }
}
