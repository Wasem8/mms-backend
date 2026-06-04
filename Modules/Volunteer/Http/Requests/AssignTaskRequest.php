<?php

namespace Modules\Volunteer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Volunteer\DTOs\AssignTaskDTO;

class AssignTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'application_id'   => ['required', 'integer', 'exists:volunteer_applications,id'],
            'task_description' => ['required', 'string'],
        ];
    }

    public function toDTO(): AssignTaskDTO
    {
        return new AssignTaskDTO(
            applicationId: (int) $this->input('application_id'),
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
