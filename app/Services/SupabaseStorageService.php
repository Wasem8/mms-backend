<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class SupabaseStorageService
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

    /**
     * Upload a single file and return its public URL.
     */
    public function upload(UploadedFile $file, string $folder = ''): string
    {
        $fileName = $this->generateFileName($file);
        $path     = $this->buildPath($fileName, $folder);

        $uploadUrl = $this->baseUrl . '/storage/v1/object/' . $path;

        $response = Http::withHeaders([
            'apikey'        => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
        ])->attach(
            'file',
            file_get_contents($file->getRealPath()),
            $fileName
        )->post($uploadUrl);

        if (! $response->successful()) {
            throw new \RuntimeException('Supabase upload failed: ' . $response->body());
        }

        return $this->baseUrl . '/storage/v1/object/public/' . $path;
    }

    /**
     * Upload multiple files and return an array of public URLs.
     *
     * @param  UploadedFile[]  $files
     * @return string[]
     */
    public function uploadMany(array $files, string $folder = ''): array
    {
        return array_map(
            fn(UploadedFile $file) => $this->upload($file, $folder),
            $files
        );
    }

    // ─── Private helpers ──────────────────────────────────────────────────

    private function generateFileName(UploadedFile $file): string
    {
        return uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
    }

    private function buildPath(string $fileName, string $folder): string
    {
        $folder = trim($folder, '/');

        return $folder
            ? $this->bucket . '/' . $folder . '/' . $fileName
            : $this->bucket . '/' . $fileName;
    }
}
