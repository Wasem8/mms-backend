<?php

namespace Modules\Complaint\Http\Requests;

use App\Services\SupabaseStorageService;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Complaint\DTO\CreateMaintenanceRequestDTO;

class CreateMaintenanceRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category'    => ['required', 'string', 'in:hvac,electrical,plumbing,sound_system,general'],
            // Accept urgency levels as strings: low, medium, high, urgent
            'is_urgent'   => ['sometimes', 'in:low,medium,high,urgent'],

            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function toDTO(): CreateMaintenanceRequestDTO
    {
        $authUser = auth()->user();
        $mosqueId = $authUser?->mosque_id;

        if (! $mosqueId) {
            abort(403, 'Unauthorized mosque access.');
        }

        $attachmentUrls = [];

        if ($this->hasFile('attachments')) {
            $files = $this->file('attachments');

            $files = is_array($files) ? $files : [$files];

            $attachmentUrls = app(SupabaseStorageService::class)
                ->uploadMany(files: $files, folder: 'maintenance-requests');
        }

        return new CreateMaintenanceRequestDTO(
            mosqueId: $mosqueId,
            title: $this->string('title')->toString(),
            description: $this->string('description')->toString(),
            category: $this->string('category')->toString(),
            isUrgent: $this->input('is_urgent', 'low'),
            attachments: $attachmentUrls ?: null,
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
