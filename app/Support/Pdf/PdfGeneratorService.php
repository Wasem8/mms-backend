<?php

namespace App\Support\Pdf;

use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class PdfGeneratorService
{
    /**
     * سجل الخطوط المدعومة. كل خط له ملفات Regular/Bold مستضافة على Supabase
     * ويُنزَّل لـ /tmp عند أول استخدام في كل cold start.
     */
    private const FONTS = [
        'cairo' => [
            'R' => 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Regular.ttf',
            'B' => 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Bold.ttf',
        ],
        'xbriyaz' => [
            // 📝 عدّل الروابط لتطابق مسار ملفات xbriyaz الفعلي على Supabase
            'R' => 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/xbriyaz-Regular.ttf',
            'B' => 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/xbriyaz-Bold.ttf',
        ],
    ];

    /**
     * توليد PDF من HTML باستخدام mPDF مع إعدادات معزولة تماماً
     * (متوافقة مع بيئة Vercel serverless — كل حاجة بتتكتب في /tmp)
     *
     * @param string $fontKey مفتاح الخط من self::FONTS (مثلاً 'cairo' أو 'xbriyaz')
     */
    public function generate(string $html, string $cacheKey = 'default', string $fontKey = 'cairo'): string
    {
        if (!isset(self::FONTS[$fontKey])) {
            throw new \InvalidArgumentException("Unknown PDF font key: {$fontKey}");
        }

        $tempDir = "/tmp/mpdf_cache_{$cacheKey}";

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            $regularFile = "{$fontKey}-Regular.ttf";
            $boldFile    = "{$fontKey}-Bold.ttf";

            // تحميل ملفات الخط لمجلد /tmp المؤقت (يشتغل مرة واحدة فقط لكل cold start)
            $this->ensureFont(self::FONTS[$fontKey]['R'], "/tmp/{$regularFile}");
            $this->ensureFont(self::FONTS[$fontKey]['B'], "/tmp/{$boldFile}");

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
                    $fontKey => [
                        'R'      => $regularFile,
                        'B'      => $boldFile,
                        'useOTL' => 0xFF,
                    ],
                ],
                'default_font' => $fontKey,
            ]);

            $mpdf->WriteHTML($html);

            return $mpdf->Output('', 'S');
        } catch (\Throwable $e) {
            Log::error('mPDF generation failed', [
                'cacheKey' => $cacheKey,
                'fontKey'  => $fontKey,
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
