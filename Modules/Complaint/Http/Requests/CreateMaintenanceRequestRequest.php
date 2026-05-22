<?php

namespace Modules\Complaint\Http\Requests;

use App\Services\SupabaseStorageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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
        $files = $this->hasFile('attachments')
            ? (is_array($this->file('attachments'))
                ? $this->file('attachments')
                : [$this->file('attachments')])
            : [];

        $validFiles = array_values(
            array_filter($files, fn($f) => $f instanceof UploadedFile && $f->isValid())
        );

        return new CreateMaintenanceRequestDTO(
            mosqueId: $this->integer('mosque_id'),
            title: $this->string('title')->toString(),
            description: $this->string('description')->toString(),
            category: $this->string('category')->toString(),
            urgency: $this->string('urgency', 'low')->toString(),
            attachments: $validFiles,
        );
    }

    public function authorize(): bool
    {
        return true;
    }
}
