<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'halaqa_id' => 'required|exists:halaqats,id',
            'student_id' => 'required|exists:students,id',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'evaluated_at' => 'nullable|date',
            'surah_name'   => 'required|string|max:100',
            'from_ayah'    => 'required|integer|min:1',
            'to_ayah'      => 'required|integer|min:1|gte:from_ayah',
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
