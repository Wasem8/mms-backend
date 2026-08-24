<?php

namespace Modules\Volunteer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Volunteer\DTOs\CreateOpportunityDTO;


class CreateOpportunityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['required', 'string'],
            'required_volunteers' => ['required', 'integer', 'min:1'],
            'start_date'          => ['required', 'date', 'after_or_equal:today'],
            'end_date'            => ['required', 'date', 'after:start_date'],
            'tasks'               => ['sometimes', 'array'],
            'tasks.*'             => ['string', 'max:1000'],
        ];
    }

    public function toDTO(): CreateOpportunityDTO
    {
        $mosque = $this->user()->managedMosque;

        if (! $mosque) {
            abort(422, __('messages.no_mosque_assigned_to_manager'));
        }

        return new CreateOpportunityDTO(
            mosqueId: (int) $mosque->id,
            title: $this->string('title'),
            description: $this->string('description'),
            requiredVolunteers: (int) $this->input('required_volunteers'),
            startDate: $this->string('start_date'),
            endDate: $this->string('end_date'),
            tasks: $this->input('tasks', []),
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
