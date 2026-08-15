<?php

namespace Modules\Donation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mosque_id' => 'sometimes|required|exists:mosques,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'sometimes|required|numeric|min:0',
            'collected_amount' => 'nullable|numeric|min:0',
            'status' => 'sometimes|required|in:active,paused,completed,cancelled',
            'priority'      => ['nullable', 'in:high,medium,low'],
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|nullable|date',
            'cover_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $endDate   = $this->input('end_date');

            if ($endDate && !$startDate) {
                $campaign = \Modules\Donation\Models\Campaign::find($this->route('id'));
                $startDate = $campaign ? $campaign->start_date : null;
            }

            if ($startDate && $endDate && $endDate < $startDate) {
                $validator->errors()->add('end_date', 'The end_date must be after or equal to the start_date.');
            }
        });
    }
}
