<?php

namespace App\Support\Storage;

use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    public function uploadPdf(string $pdfContent, string $fileName, string $bucket): void
    {
        $baseUrl = config('services.supabase.url');
        $key     = config('services.supabase.key');

        $uploadUrl = "{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}";

        $response = Http::retry(3, 1000)
            ->timeout(60)
            ->withHeaders([
                'apikey'        => $key,
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/pdf',
            ])
            ->withBody($pdfContent, 'application/pdf')
            ->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception('Supabase Upload Failed: ' . $response->body());
        }
    }

    public function createSignedUrl(string $fileName, string $bucket, int $expiresIn = 3600): string
    {
        $baseUrl = config('services.supabase.url');
        $key     = config('services.supabase.key');

        $response = Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->post(
            "{$baseUrl}/storage/v1/object/sign/{$bucket}/{$fileName}",
            ['expiresIn' => $expiresIn]
        );

        if (!$response->successful()) {
            throw new \Exception('Failed To Create Signed URL: ' . $response->body());
        }

        return $baseUrl . '/storage/v1' . $response->json('signedURL');
    }
}
