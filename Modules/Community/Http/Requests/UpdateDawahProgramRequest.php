<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDawahProgramRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
           // 'mosque_id' => 'sometimes|required|exists:mosques,id',
            'space_id' => 'sometimes|required|exists:mosque_spaces,id',
            'program_name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'presenter' => 'nullable|string',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'date' => 'sometimes|required|date',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
