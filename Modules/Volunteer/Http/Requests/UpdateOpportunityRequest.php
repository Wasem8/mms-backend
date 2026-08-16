<?php

namespace Modules\Volunteer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Volunteer\DTOs\UpdateOpportunityDTO;

class UpdateOpportunityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'               => ['sometimes', 'string', 'max:255'],
            'description'         => ['sometimes', 'string'],
            'required_volunteers' => ['sometimes', 'integer', 'min:1'],
            'start_date'          => ['sometimes', 'date'],
            'end_date'            => ['sometimes', 'date', 'after:start_date'],
            'tasks'               => ['sometimes', 'array'],
            'tasks.*'             => ['string', 'max:1000'],
        ];
    }

    public function toDTO(): UpdateOpportunityDTO
    {
        return new UpdateOpportunityDTO(
            title: $this->has('title')               ? $this->string('title')                       : null,
            description: $this->has('description')         ? $this->string('description')                 : null,
            requiredVolunteers: $this->has('required_volunteers') ? (int) $this->input('required_volunteers')    : null,
            startDate: $this->has('start_date')          ? $this->string('start_date')                  : null,
            endDate: $this->has('end_date')            ? $this->string('end_date')                    : null,
            tasks: $this->has('tasks')                ? $this->input('tasks')                       : null,
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
