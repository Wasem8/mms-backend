<?php

namespace Modules\Volunteer\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Modules\Volunteer\DTOs\CreateTaskDTO;

class CreateTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'task_description' => ['required', 'string'],
        ];
    }

    public function toDTO(int $opportunityId): CreateTaskDTO
    {
        return new CreateTaskDTO(
            opportunityId: $opportunityId,
            taskDescription: $this->string('task_description'),
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
