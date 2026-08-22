<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    public function uploadPdf(
        string $pdfContent,
        string $fileName,
        bool $upsert = true
    ): void {

        set_time_limit(120);

        $baseUrl = config('services.supabase.url');
        $key     = config('services.supabase.service_role_key');
        $bucket  = config('services.supabase.reports_bucket');

        $uploadUrl = "{$baseUrl}/storage/v1/object/{$bucket}/{$fileName}";

        $response = Http::retry(3, 1000)
            ->timeout(30)
            ->withHeaders([
                'apikey'        => $key,
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/pdf',
                // ✅ نفس سلوك خدمة التبرعات العاملة: السماح بالكتابة فوق ملف موجود
                'x-upsert'      => $upsert ? 'true' : 'false',
            ])
            ->withBody($pdfContent, 'application/pdf')
            ->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception(
                'Supabase Upload Failed: ' . $response->body()
            );
        }
    }

    public function createSignedUrl(
        string $fileName,
        int $expiresIn = 3600
    ): string {

        set_time_limit(120);

        $baseUrl = config('services.supabase.url');
        $key     = config('services.supabase.service_role_key');
        $bucket  = config('services.supabase.reports_bucket');

        $response = Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->timeout(30)->post(
            "{$baseUrl}/storage/v1/object/sign/{$bucket}/{$fileName}",
            ['expiresIn' => $expiresIn]
        );

        if (!$response->successful()) {
            throw new \Exception(
                'Failed To Create Signed URL: ' . $response->body()
            );
        }

        return $baseUrl . '/storage/v1' . $response->json('signedURL');
    }
}
