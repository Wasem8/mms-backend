<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SupabaseStorageService
{
    private string $baseUrl;
    private string $bucket;
    private string $key;

    public function __construct()
    {
        $this->baseUrl = config('services.supabase.url')
            ?? throw new RuntimeException('SUPABASE_URL is not set in environment.');

        $this->bucket  = config('services.supabase.bucket')
            ?? throw new RuntimeException('SUPABASE_BUCKET is not set in environment.');

        $this->key     = config('services.supabase.key')
            ?? throw new RuntimeException('SUPABASE_KEY is not set in environment.');
    }

    /**
     * Upload a single file and return its public URL.
     */
    public function upload(UploadedFile $file, string $folder = ''): string
    {
        $fileName  = $this->generateFileName($file);
        $path      = $this->buildPath($fileName, $folder);
        $uploadUrl = $this->baseUrl . '/storage/v1/object/' . $path;

        // getRealPath() can return false on read-only serverless filesystems
        // (e.g. Vercel). Use the stream from the UploadedFile directly instead.
        $stream = fopen($file->getRealPath() ?: $file->getPathname(), 'rb');

        if ($stream === false) {
            throw new RuntimeException(
                "Cannot open uploaded file [{$file->getClientOriginalName()}] for reading."
            );
        }

        $response = Http::withHeaders([
            'apikey'        => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type'  => $file->getMimeType(),
        ])->withBody(
            stream_get_contents($stream),
            $file->getMimeType(),
        )->post($uploadUrl);

        fclose($stream);

        if (! $response->successful()) {
            Log::error('Supabase Upload Error', [
                'file'   => $file->getClientOriginalName(),
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException(
                "Supabase upload failed for [{$file->getClientOriginalName()}]: " . $response->body()
            );
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
            $files,
        );
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

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
