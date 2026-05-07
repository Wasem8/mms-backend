<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDawahProgramRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
         //   'mosque_id' => 'required|exists:mosques,id',
            'space_id' => 'required|exists:mosque_spaces,id',
            'program_name' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'presenter' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'date' => 'required|date',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
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
