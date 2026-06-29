<?php

namespace Modules\Dashboard\Services;

class PdfGeneratorService
{
    public function generate(string $html): string
    {
        // 1. تحديد وتأمين مجلد الكاش بداخل /tmp لـ Vercel
        $tempDir = '/tmp/mpdf_cache_core';

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        if (!defined('_MPDF_TEMP_DIR')) {
            define('_MPDF_TEMP_DIR', $tempDir);
        }

        try {
            // 2. تحميل خطوط القاهرة للمجلد المؤقت /tmp
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

            // 3. بناء إعدادات مستقلة تماماً 100% دون استدعاء أي كلاسات داخلية من mPDF
            // هذا يحميك تماماً من مشاكل الـ Autoloading والـ Exclude في فيرسيل
            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 8,
                'margin_bottom' => 8,
                'tempDir'       => $tempDir,

                // نخبر المكتبة بالبحث عن الخطوط بداخل مجلد /tmp فقط (المجلد الذي يحتوي على خط القاهرة المرفوع)
                'fontDir' => ['/tmp'],

                // تمرير مصفوفة الخطوط الصريحة والوحيدة المتاحة للطباعة
                'fontdata' => [
                    'cairo' => [
                        'R'      => 'Cairo-Regular.ttf',
                        'B'      => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                    ]
                ],
                'default_font' => 'cairo'
            ]);

            // 4. ضخ الـ HTML وتوليد الباينري للرفع إلى سوبابيس
            $mpdf->WriteHTML($html);
            return $mpdf->Output('', 'S');

        } catch (\Throwable $e) {
            \Log::error('mPDF VERCEL ULTIMATE ISOLATED ERROR', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
