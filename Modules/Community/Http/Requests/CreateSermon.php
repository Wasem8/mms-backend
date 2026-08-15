<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Community\Models\Sermon;

class CreateSermon extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'speaker_name' => 'required|string',
            'sermon_date' => 'required|date|after_or_equal:today',
            'attachments'        => ['nullable', 'array', 'max:10'],
            'attachments.*'      => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
            'category' => ['required', 'string', Rule::in(Sermon::CATEGORIES)]

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
