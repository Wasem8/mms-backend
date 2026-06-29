<?php

namespace Modules\Volunteer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Volunteer\DTOs\LogHoursDTO;

class LogHoursRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'volunteer_id'       => ['required', 'integer', 'exists:users,id'],
            'opportunity_id'     => ['required', 'integer', 'exists:volunteer_opportunities,id'],
            'logged_hours'       => ['required', 'numeric', 'min:0.25', 'max:24'],
            'manager_evaluation' => ['required', 'string', 'max:1000'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toDTO(): LogHoursDTO
    {
        return new LogHoursDTO(
            volunteerId: (int)   $this->input('volunteer_id'),
            opportunityId: (int)   $this->input('opportunity_id'),
            loggedHours: (float) $this->input('logged_hours'),
            managerEvaluation: $this->string('manager_evaluation'),
            notes: $this->string('notes') ?: null,
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
