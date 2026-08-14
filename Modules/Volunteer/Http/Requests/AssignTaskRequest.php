<?php

namespace Modules\Volunteer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Volunteer\DTOs\AssignTaskDTO;

class AssignTaskRequest extends FormRequest
{
    
    public function rules(): array
    {
        return [
            'application_id' => ['required', 'integer', 'exists:volunteer_applications,id'],
        ];
    }

    public function toDTO(int $taskId): AssignTaskDTO
    {
        return new AssignTaskDTO(
            taskId: $taskId,
            applicationId: (int) $this->input('application_id'),
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
