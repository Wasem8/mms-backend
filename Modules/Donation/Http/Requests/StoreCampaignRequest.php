<?php

namespace Modules\Donation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mosque_id' => 'nullable|exists:mosques,id',

            'title' => 'required|string|max:255',
            'description' => 'nullable|string',

            'target_amount' => 'required|numeric|min:0',

            'status' => 'required|in:active,paused,completed,cancelled',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority'      => ['nullable', 'in:high,medium,low'],
            'cover_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
