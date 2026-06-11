<?php

namespace Modules\Education\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Modules\Education\Models\MediaUpload;
use Exception;

class MediaUploadService
{
    public function uploadVoice(UploadedFile $file): MediaUpload
    {
        $bucket = config('services.supabase.voices');
        $url = config('services.supabase.url');
        $key = config('services.supabase.key');

        if (empty($bucket) || empty($url) || empty($key)) {
            throw new Exception('Supabase config missing', 500);
        }

        $fileName = 'voice_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $key,
            'apikey' => $key,
        ])->attach(
            'file',
            file_get_contents($file->getRealPath()),
            $fileName
        )->post("{$url}/storage/v1/object/{$bucket}/{$fileName}");

        if (!$response->successful()) {
            throw new Exception('Failed to upload voice to Supabase: ' . $response->body());
        }

        $publicUrl = "{$url}/storage/v1/object/public/{$bucket}/{$fileName}";

        return MediaUpload::create([
            'user_id' => auth()->id(),
            'path' => $fileName,
            'url' => $publicUrl,
            'type' => 'voice_note',
        ]);
    }
}
