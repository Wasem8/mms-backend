<?php

namespace Modules\Complaint\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Complaint\DTO\ProcessMaintenanceRequestDTO;

class ProcessMaintenanceRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:in_progress,completed,rejected'],

            // rejection_reason is required only when the new status is 'rejected'.
            'rejection_reason' => [
                'nullable',
                'string',
                'max:1000',
                'required_if:status,rejected',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required_if' =>
            'A rejection reason is required when rejecting a request.',
        ];
    }

    public function toDTO(): ProcessMaintenanceRequestDTO
    {
        return new ProcessMaintenanceRequestDTO(
            status: $this->string('status')->toString(),
            regionManagerId: $this->user()->id,
            rejectionReason: $this->input('rejection_reason'),
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
