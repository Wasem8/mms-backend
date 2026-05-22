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

    private function storeAttachments(): array
    {
        if (! $this->hasFile('attachments')) {
            return [];
        }

        $files = $this->file('attachments');
        $files = is_array($files) ? $files : [$files];

        return array_values(array_map(
            fn(UploadedFile $file) => $this->uploadToSupabase($file),
            array_filter($files, fn($f) => $f instanceof UploadedFile && $f->isValid()),
        ));
    }

    private function uploadToSupabase(UploadedFile $file): string
    {
        $fileName  = uniqid() . '.' . $file->getClientOriginalExtension();
        $baseUrl   = config('services.supabase.url');
        $bucket    = config('services.supabase.bucket');
        $key       = config('services.supabase.key');
        $path      = $bucket . '/maintenance-attachments/' . $fileName;
        $uploadUrl = $baseUrl . '/storage/v1/object/' . $path;

        $response = Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->attach(
            'file',
            file_get_contents($file),
            $fileName,
        )->post($uploadUrl);

        if (! $response->successful()) {
            throw new \RuntimeException('Upload failed: ' . $response->body());
        }

        return $baseUrl . '/storage/v1/object/public/' . $path;
    }
}/
    public function authorize(): bool
    {
        return true;
    }
}
