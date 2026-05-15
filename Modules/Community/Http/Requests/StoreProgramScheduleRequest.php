<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'      => ['nullable', 'string', 'max:255'],
            'notes'      => ['nullable', 'string'],
            'date'       => ['required', 'date', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
