<?php

namespace Modules\Complaint\Http\Requests;

use App\Services\SupabaseStorageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Modules\Complaint\DTO\CreateMaintenanceRequestDTO;

class CreateMaintenanceRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */

    public function rules(): array
    {
        return [
            'mosque_id'   => ['required', 'integer', 'exists:mosques,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category'    => ['required', 'string', 'in:hvac,electrical,plumbing,sound_system,general'],
            'urgency'     => ['required', 'string', 'in:low,medium,high,urgent'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],        ];
    }
    public function toDTO(): CreateMaintenanceRequestDTO
    {
        return new CreateMaintenanceRequestDTO(
            mosqueId: $this->integer('mosque_id'),
            title: $this->string('title')->toString(),
            description: $this->string('description')->toString(),
            category: $this->string('category')->toString(),
            urgency: $this->string('urgency', 'low')->toString(),
            attachments: $this->storeAttachments(),
        );
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    /**
     * Upload every valid file to Supabase and return their public URLs.
     *
     * @return string[]
     */
    private function storeAttachments(): array
    {
        if (! $this->hasFile('attachments')) {
            return [];
        }

        $files = $this->file('attachments');
        $files = is_array($files) ? $files : [$files];

        // Filter out any invalid uploads before handing off to Supabase
        $validFiles = array_values(
            array_filter(
                $files,
                fn($file) => $file instanceof UploadedFile && $file->isValid(),
            )
        );

        if (empty($validFiles)) {
            return [];
        }

        /** @var SupabaseStorageService $supabase */
        $supabase = app(SupabaseStorageService::class);

        // uploadMany() returns public URLs — stored directly on the model as JSON
        return $supabase->uploadMany($validFiles, folder: 'maintenance-attachments');
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
