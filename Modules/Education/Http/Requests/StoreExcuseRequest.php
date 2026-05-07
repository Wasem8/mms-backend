<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExcuseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id'   => [
                'required',
                Rule::exists('students', 'id')->where('parent_id', auth()->id())
            ],
            'halaqa_id'    => 'required|exists:halaqats,id',
            'absence_date' => 'required|date|after_or_equal:today',
            'reason'       => 'required|string|min:10|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'absence_date.after_or_equal' => 'لا يمكن تقديم عذر لتاريخ في الماضي.',
            'student_id.exists'           => 'هذا الطالب غير مسجل ضمن أبنائك.',
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
