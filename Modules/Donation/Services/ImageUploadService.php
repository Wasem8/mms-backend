<?php

namespace Modules\Donation\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class ImageUploadService
{
    private string $baseUrl;
    private string $bucket;
    private string $key;

    public function __construct()
    {
        $this->baseUrl = config('services.supabase.url');
        $this->bucket  = config('services.supabase.bucket');
        $this->key     = config('services.supabase.key');
    }

    public function upload(UploadedFile $image): string
    {
        $fileName  = uniqid('campaign_', true) . '.' . $image->getClientOriginalExtension();
        $path      = "{$this->bucket}/{$fileName}";
        $uploadUrl = "{$this->baseUrl}/storage/v1/object/{$path}";

        $response = Http::withHeaders([
            'apikey'        => $this->key,
            'Authorization' => "Bearer {$this->key}",
        ])->attach('file', file_get_contents($image), $fileName)->post($uploadUrl);

        throw_unless(
            $response->successful(),
            \RuntimeException::class,
            'Image upload failed: ' . $response->body()
        );

        return "{$this->baseUrl}/storage/v1/object/public/{$path}";
    }

    public function delete(string $url): void
    {
        $fileName  = basename($url);
        $path      = "{$this->bucket}/{$fileName}";
        $deleteUrl = "{$this->baseUrl}/storage/v1/object/{$path}";

        Http::withHeaders([
            'apikey'        => $this->key,
            'Authorization' => "Bearer {$this->key}",
        ])->delete($deleteUrl);
    }
}
