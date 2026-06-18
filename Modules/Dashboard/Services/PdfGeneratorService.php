<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Facades\Log;

class PdfGeneratorService
{
    public function generate(string $html): string
    {
        // 1. تحديد مجلد الكاش بداخل /tmp وهو المجلد الوحيد المتاح للكتابة في Vercel
        $tempDir = '/tmp/mpdf_cache_core';

        // التحقق من وجود المجلد، وإذا لم يكن موجوداً يتم إنشاؤه بصلاحيات كاملة
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // 2. تعريف ثوابت مكتبة mPDF للمجلد المؤقت إذا لم تكن معرّفة مسبقاً
        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            // 3. تحميل خطوط القاهرة من السيرفر السحابي وحفظها مؤقتاً في /tmp
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

            // 4. جلب الإعدادات الافتراضية للمكتبة للخطوط والمجلدات
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            // 5. تهيئة كائن mPDF وتمرير الـ tempDir والـ fontDir بشكل صريح
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir, // 🎯 تمرير المجلد المؤقت الآمن هنا
                'fontDir'       => array_merge($fontDirs, ['/tmp']), // 🎯 البحث عن الخطوط في /tmp
                'fontdata'      => array_merge($fontData, [
                    'cairo' => [
                        'R'      => 'Cairo-Regular.ttf',
                        'B'      => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                    ]
                ]),
                'default_font' => 'cairo'
            ]);

            // 6. ضخ الـ HTML وتوليد الملف كـ سِلسِلة باينري (Binary String) للرفع
            $mpdf->WriteHTML($html);
            return $mpdf->Output('', 'S');

        } catch (\Throwable $e) {
            Log::error('mPDF VERCEL SERVICE ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
