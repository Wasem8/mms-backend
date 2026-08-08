<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Mosque\DTOs\CreateMosqueTaskDTO;
use Modules\Mosque\Enums\MosqueTaskCategory;
use Modules\Mosque\Models\Mosque;

class StoreMosqueTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // role:mosque_manager handled in route middleware
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['required', Rule::in(array_column(MosqueTaskCategory::cases(), 'value'))],
            'due_date'     => ['nullable', 'date'],
            'due_time'     => ['nullable', 'date_format:H:i'],
            'is_important' => ['nullable', 'boolean'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ];
    }
    public function toDTO(): CreateMosqueTaskDTO
    {
        $mosque = Mosque::managedBy(Auth::id())->firstOrFail();

        return new CreateMosqueTaskDTO(
            mosqueId: $mosque->id,
            createdBy: Auth::id(),
            title: $this->validated('title'),
            category: $this->validated('category'),
            dueDate: $this->validated('due_date') ?? now()->toDateString(),
            dueTime: $this->validated('due_time'),
            isImportant: $this->boolean('is_important'),
            notes: $this->validated('notes'),
        );
    }
}
