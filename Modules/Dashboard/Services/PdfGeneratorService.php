<?php

namespace Modules\Dashboard\Services;

class PdfGeneratorService
{
    public function generate(string $html): string
    {
        // 1. تحديد مجلد الكاش بداخل /tmp لـ Vercel
        $tempDir = '/tmp/mpdf_cache_core';

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            // 2. تحميل خطوط القاهرة وحفظها مؤقتاً في /tmp
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

            // 3. تهيئة إعدادات mPDF بدون استدعاء كلاسات ConfigVariables لتفادي الـ Crash
            // نمرر المجلدات والخطوط مباشرة للمكتبة وهي ستقوم بدمجها داخلياً تلقائياً
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir,

                // نمرر المجلد الأساسي للمكتبة ومجلد /tmp الإضافي للخطوط
                'fontDir' => array_merge([
                    __DIR__ . '/../../../../vendor/mpdf/mpdf/ttfonts' // المسار الافتراضي لخطوط المكتبة بداخل الـ vendor
                ], ['/tmp']),

                // تعريف الخطوط مسبقاً بطريقة آمنة ومتوافقة مع الإصدارات المختلفة
                'fontdata' => [
                    'cairo' => [
                        'R'      => 'Cairo-Regular.ttf',
                        'B'      => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                    ],
                    // الاحتفاظ بالخطوط الافتراضية الهامة للمكتبة منعاً لأي خلل داخلي
                    'dejavusanscondensed' => [
                        'R' => 'DejaVuSansCondensed.ttf',
                        'B' => 'DejaVuSansCondensed-Bold.ttf',
                        'I' => 'DejaVuSansCondensed-Oblique.ttf',
                        'BI' => 'DejaVuSansCondensed-BoldOblique.ttf',
                        'useOTL' => 0xFF,
                        'useKashida' => 75,
                    ]
                ],
                'default_font' => 'cairo'
            ]);

            // 4. ضخ الـ HTML وتوليد الملف كـ سِلسِلة باينري (Binary String) للرفع
            $mpdf->WriteHTML($html);
            return $mpdf->Output('', 'S');

        } catch (\Throwable $e) {
            \Log::error('mPDF VERCEL SERVICE NO-CONFIG ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
