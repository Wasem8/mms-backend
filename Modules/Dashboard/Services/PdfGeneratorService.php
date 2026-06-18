<?php

namespace Modules\Dashboard\Services;

class PdfGeneratorService
{
    public function generate(string $html): string
    {
        $tempDir = '/tmp/mpdf_cache_core';

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            // 1. تحميل خطوط القاهرة للمجلد المؤقت /tmp
            $remoteRegularUrl = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Regular.ttf';
            $remoteBoldUrl = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Bold.ttf';

            $localRegularPath = '/tmp/Cairo-Regular.ttf';
            $localBoldPath = '/tmp/Cairo-Bold.ttf';

            if (!file_exists($localRegularPath)) {
                @file_put_contents($localRegularPath, @file_get_contents($remoteRegularUrl));
            }
            if (!file_exists($localBoldPath)) {
                @file_put_contents($localBoldPath, @file_get_contents($remoteBoldUrl));
            }

            // 2. جلب المجلدات الافتراضية بأمان (الملفات البرمجية مرفوعة الآن)
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            // 3. بناء مصفوفة الخطوط يدوياً بدلاً من دمج المصفوفة الكبيرة لمنع استدعاء الخطوط المحذوفة
            $fontData = [
                'cairo' => [
                    'R'      => 'Cairo-Regular.ttf',
                    'B'      => 'Cairo-Bold.ttf',
                    'useOTL' => 0xFF,
                ],
                'dejavusanscondensed' => [
                    'R' => 'DejaVuSansCondensed.ttf',
                    'B' => 'DejaVuSansCondensed-Bold.ttf',
                    'I' => 'DejaVuSansCondensed-Oblique.ttf',
                    'BI' => 'DejaVuSansCondensed-BoldOblique.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ];

            // 4. تهيئة mPDF بوزن خفيف جداً ومتوافق مع Vercel
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir,
                'fontDir'       => array_merge($fontDirs, ['/tmp']),
                'fontdata'      => $fontData,
                'default_font'  => 'cairo'
            ]);

            $mpdf->WriteHTML($html);
            return $mpdf->Output('', 'S');

        } catch (\Throwable $e) {
            \Log::error('mPDF VERCEL COMPRESSED CONFIG ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
