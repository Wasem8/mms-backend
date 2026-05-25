<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\User\Models\User;

class UpdateHalaqaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'teacher_id' => [
                'nullable',
                'integer',

                // المعلم موجود
                Rule::exists('users', 'id'),

                // المعلم يملك role teacher
                function ($attribute, $value, $fail) {

                    if (!$value) {
                        return;
                    }

                    $teacher = User::find($value);

                    if (!$teacher) {
                        return;
                    }

                    if (
                        !$teacher->hasRole('teacher')
                        && !$teacher->isTeacher()
                    ) {
                        $fail(__('messages.user_not_teacher'));
                    }

                    // منع تعيين معلم من مسجد آخر
                    $user = auth()->user();

                    if (
                        $user->mosque_id &&
                        $teacher->mosque_id !== $user->mosque_id
                    ) {
                        $fail(__('messages.teacher_another_mosque'));
                    }

                    // منع تعيين معلم غير نشط
                    if ($teacher->status !== 'active') {
                        $fail(__('messages.teacher_not_active'));
                    }
                },
            ],

            'capacity' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'schedule_days' => [
                'nullable',
                'array',
            ],

            'schedule_days.*' => [
                'string',
                Rule::in([
                    'sunday',
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday',
                ]),
            ],

            'start_time' => [
                'sometimes',
                'required',
                'date_format:H:i:s',
            ],

            'end_time' => [
                'sometimes',
                'required',
                'date_format:H:i:s',
                'after:start_time',
            ],

            'status' => [
                'sometimes',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [

        ];
    }
}
