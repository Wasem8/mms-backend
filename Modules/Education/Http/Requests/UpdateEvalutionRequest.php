<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvalutionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'score' => 'sometimes|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'dimensions.tajweed' => 'nullable|in:excellent,good,needs_work',
            'dimensions.hifz' => 'nullable|in:excellent,good,needs_work',
            'dimensions.fluency' => 'nullable|in:excellent,good,needs_work',
            'dimensions.makharij' => 'nullable|in:excellent,good,needs_work',
            'evaluated_at' => 'sometimes|date',
            'surah_name'   => 'sometimes|string|max:100',
            'from_ayah'    => 'sometimes|integer|min:1',
            'to_ayah'      => 'sometimes|integer|min:1|gte:from_ayah',
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
