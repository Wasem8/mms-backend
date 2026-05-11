<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMosqueNeedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mosque_id' => 'required|exists:mosques,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:financial,service,maintenance',
            'target_amount' => 'nullable|numeric|min:0',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_urgent' => 'in:true,false,1,0',
            'status' => 'nullable|in:open,partially_fulfilled,fulfilled'
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
