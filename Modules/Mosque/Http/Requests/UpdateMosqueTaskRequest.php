<?php

namespace Modules\Mosque\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Mosque\DTOs\UpdateMosqueTaskDTO;
use Modules\Mosque\Enums\MosqueTaskCategory;

class UpdateMosqueTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'max:255'],
            'category'     => ['sometimes', Rule::in(array_column(MosqueTaskCategory::cases(), 'value'))],
            'due_date'     => ['sometimes', 'date'],
            'due_time'     => ['sometimes', 'nullable', 'date_format:H:i'],
            'is_important' => ['sometimes', 'boolean'],
            'notes'        => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function toDTO(): UpdateMosqueTaskDTO
    {
        return new UpdateMosqueTaskDTO(
            title: $this->validated('title'),
            category: $this->validated('category'),
            dueDate: $this->validated('due_date'),
            dueTime: $this->validated('due_time'),
            isImportant: $this->has('is_important') ? $this->boolean('is_important') : null,
            notes: $this->validated('notes'),
        );
    }
}
