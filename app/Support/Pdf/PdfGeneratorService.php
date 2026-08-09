<?php

namespace App\Support\Pdf;

use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class PdfGeneratorService
{
    /**
     * توليد PDF من HTML باستخدام mPDF مع إعدادات معزولة تماماً
     * (متوافقة مع بيئة Vercel serverless — كل حاجة بتتكتب في /tmp)
     */
    public function generate(string $html, string $cacheKey = 'default'): string
    {
        $tempDir = "/tmp/mpdf_cache_{$cacheKey}";

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            // تحميل خطوط Cairo لمجلد /tmp المؤقت (يشتغل مرة واحدة فقط لكل cold start)
            $this->ensureFont(
                'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Regular.ttf',
                '/tmp/Cairo-Regular.ttf'
            );
            $this->ensureFont(
                'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Bold.ttf',
                '/tmp/Cairo-Bold.ttf'
            );

            $mpdf = new Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir,
                'fontDir'       => ['/tmp'],
                'fontdata'      => [
                    'cairo' => [
                        'R'      => 'Cairo-Regular.ttf',
                        'B'      => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                    ],
                ],
                'default_font' => 'cairo',
            ]);

            $mpdf->WriteHTML($html);

            return $mpdf->Output('', 'S');
        } catch (\Throwable $e) {
            Log::error('mPDF generation failed', [
                'cacheKey' => $cacheKey,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function ensureFont(string $url, string $path): void
    {
        if (!file_exists($path)) {
            @file_put_contents($path, @file_get_contents($url));
        }
    }
}
