<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

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
            // تحميل خطوط القاهرة للمجلد المؤقت
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

            // الآن بعد إلغاء الـ exclude، يمكننا جلب الإعدادات الافتراضية بأمان التام بدون كراش
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir,
                'fontDir'       => array_merge($fontDirs, ['/tmp']), // دمج مجلد المكتبة المرفوع مع مجلد /tmp الخاص بنا
                'fontdata'      => array_merge($fontData, [
                    'cairo' => [
                        'R'      => 'Cairo-Regular.ttf',
                        'B'      => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                    ]
                ]),
                'default_font' => 'cairo'
            ]);

            $mpdf->WriteHTML($html);
            return $mpdf->Output('', 'S');

        } catch (\Throwable $e) {
            Log::error('mPDF VERCEL FIXED CONFIG ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
