<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMosqueNeedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:financial,service,maintenance',
            'target_amount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_urgent' => 'in:true,false,1,0,true,false',
            'status' => 'sometimes|in:open,partially_fulfilled,fulfilled'
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
