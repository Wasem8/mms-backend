<?php

namespace Modules\Education\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Models\User;

class StoreHalaqaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'teacher_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);

                    if (! $user) {
                        $fail('Teacher not found.');
                    } elseif (! $user->hasRole('teacher')) {
                        $fail('User is not a teacher.');
                    }
                },
            ],
            'capacity' => 'required|integer|min:1|max:100',
            'schedule_days' => 'nullable|array',
            'schedule_days.*' => 'string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'in:active,inactive'
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
