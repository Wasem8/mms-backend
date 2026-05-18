<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDawahProgramRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */

    protected function prepareForValidation()
    {
        if ($this->has('schedules') && is_string($this->schedules)) {
            $decodedSchedules = json_decode($this->schedules, true);

            // If the JSON is valid, replace the string with the array
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'schedules' => $decodedSchedules,
                ]);
            }
        }
    }
    public function rules(): array
    {
        return [
            'space_id'        => ['sometimes', 'integer', 'exists:mosque_spaces,id'],
            'program_name'    => ['sometimes', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'type'            => ['sometimes', 'in:lecture,course,competition,other'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'presenter'       => ['sometimes', 'string', 'max:255'],
            'presenter_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_featured'     => ['sometimes', 'in:true,false,1,0'],
            'status'          => ['sometimes', 'in:active,inactive'],
            'level'           => ['sometimes', 'in:beginner,intermediate,advanced'],

            'schedules'                  => ['sometimes', 'array', 'min:1'],
            'schedules.*.title'          => ['nullable', 'string', 'max:255'],
            'schedules.*.notes'          => ['nullable', 'string'],
            'schedules.*.date'           => ['required_with:schedules', 'date', 'date_format:Y-m-d'],
            'schedules.*.start_time'     => ['required_with:schedules', 'date_format:H:i'],
            'schedules.*.end_time'       => ['required_with:schedules', 'date_format:H:i', 'after:schedules.*.start_time'],
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
