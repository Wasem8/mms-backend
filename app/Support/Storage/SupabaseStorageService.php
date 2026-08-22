<?php

namespace App\Support\Storage;

use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    public function uploadPdf(string $pdfContent, string $fileName, string $bucket, bool $upsert = false): void
    {
        $baseUrl = config('services.supabase.url');
        $key = config('services.supabase.service_role_key');
        $uploadUrl = "{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}";

        $response = Http::retry(3, 1000)
            ->timeout(60)
            ->withHeaders([
                'apikey'        => $key,
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/pdf',
                // ✅ Supabase Storage يتطلب هذا الـ header تحديداً للسماح بالكتابة فوق ملف موجود
                // (باراميتر ?upsert=true كـ query string لا يُعتمد من الـ API)
                'x-upsert'      => $upsert ? 'true' : 'false',
            ])
            ->withBody($pdfContent, 'application/pdf')
            ->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception('Supabase Upload Failed: ' . $response->body());
        }
    }
    /**
     * List objects in a bucket filtered by prefix.
     * Returns the raw array of objects (each with a `name` key).
     */
    public function listObjects(string $bucket, string $prefix): array
    {
        $baseUrl = config('services.supabase.url');
        $key = config('services.supabase.service_role_key');
                $response = Http::timeout(30)
            ->withHeaders([
                'apikey'        => $key,
                'Authorization' => 'Bearer ' . $key,
            ])
            ->post("{$baseUrl}/storage/v1/object/list/{$bucket}", [
                'prefix' => $prefix,
                'limit'  => 100,
            ]);

        if (!$response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    public function createSignedUrl(string $fileName, string $bucket, int $expiresIn = 3600): string
    {
        $baseUrl = config('services.supabase.url');
        $key = config('services.supabase.service_role_key');
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
