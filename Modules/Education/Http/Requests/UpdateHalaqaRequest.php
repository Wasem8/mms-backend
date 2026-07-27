<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\User\Models\User;
use Modules\Education\Models\Halaqa;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // تحويل أيام الأسبوع إلى أحرف صغيرة canonical lowercase تلقائياً قبل الـ Validation
        if ($this->has('schedule_days') && is_array($this->schedule_days)) {
            $this->merge([
                'schedule_days' => array_map('strtolower', $this->schedule_days),
            ]);
        }
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
                Rule::exists('users', 'id'),
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return;
                    }

                    $teacher = User::find($value);

                    if (!$teacher) {
                        return;
                    }

                    if (!$teacher->hasRole('teacher') && !$teacher->isTeacher()) {
                        $fail(__('messages.user_not_teacher'));
                    }

                    $user = auth()->user();

                    if ($user->mosque_id && $teacher->mosque_id !== $user->mosque_id) {
                        $fail(__('messages.teacher_another_mosque'));
                    }

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

            // قبول H:i:s و H:i لضمان عدم حدوث تعارض مع السواجر أو التطبيق
            'start_time' => [
                'sometimes',
                'required',
                'date_format:H:i:s,H:i',
            ],

            'end_time' => [
                'sometimes',
                'required',
                'date_format:H:i:s,H:i',
                function ($attribute, $value, $fail) {
                    $startTime = $this->input('start_time');

                    // إذا لم يُرسل start_time في الطلب، نجابه بالوقت القديم للحلقة
                    if (!$startTime) {
                        $halaqaId = $this->route('id') ?? $this->route('halaqa');
                        $halaqa = Halaqa::find($halaqaId);
                        $startTime = $halaqa?->start_time;
                    }

                    if ($startTime && strtotime($value) <= strtotime($startTime)) {
                        $fail(__('validation.after', ['attribute' => __('validation.attributes.end_time'), 'date' => __('validation.attributes.start_time')]));
                    }
                },
            ],

            'status' => [
                'sometimes',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }
}
