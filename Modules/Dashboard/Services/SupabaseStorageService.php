<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    public function uploadPdf(
        string $pdfContent,
        string $fileName
    ): void {
        $baseUrl = rtrim(config('services.supabase.url'), '/');
        $bucket  = trim(config('services.supabase.reports_bucket'), '/');
        $key     = config('services.supabase.key');

        $uploadUrl =
            "{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}";

        $response = Http::retry(3, 1000)
            ->timeout(60)
            ->withHeaders([
                'apikey'        => $key,
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/pdf',
            ])
            ->withBody(
                $pdfContent,
                'application/pdf'
            )
            ->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception(
                'Supabase PDF Upload Failed: ' .
                $response->body()
            );
        }
    }

    public function createSignedUrl(
        string $fileName,
        int $expiresIn = 3600
    ): string {
        $baseUrl = rtrim(config('services.supabase.url'), '/');
        $bucket  = trim(config('services.supabase.reports_bucket'), '/');
        $key     = config('services.supabase.key');

        $response = Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->post(
            "{$baseUrl}/storage/v1/object/sign/{$bucket}/{$fileName}",
            [
                'expiresIn' => $expiresIn,
            ]
        );

        if (!$response->successful()) {
            throw new \Exception(
                'Failed to create signed URL: ' .
                $response->body()
            );
        }

        $signedUrl = $response->json('signedURL');

        if (!$signedUrl) {
            throw new \Exception(
                'Supabase did not return signedURL.'
            );
        }

        return $baseUrl . '/storage/v1' . $signedUrl;
    }
}
