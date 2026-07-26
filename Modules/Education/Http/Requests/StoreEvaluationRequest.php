<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\Education\Enums\EvaluationLabel;

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
            'client_uuid'  => 'required|uuid',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'dimensions' => 'nullable|array',
            'dimensions.tajweed' => ['nullable', new Enum(EvaluationLabel::class)],
            'dimensions.hifz'    => ['nullable', new Enum(EvaluationLabel::class)],
            'dimensions.fluency' => ['nullable', new Enum(EvaluationLabel::class)],
            'dimensions.makharij'=> ['nullable', new Enum(EvaluationLabel::class)],
            'evaluated_at' => 'nullable|date|date_format:Y-m-d',
            'surah_name'   => 'required|string|max:100',
            'from_ayah'    => 'required|integer|min:1',
            'to_ayah'      => 'required|integer|min:1|gte:from_ayah',
            'voice_note_id' => 'nullable|integer|exists:media_uploads,id',
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
