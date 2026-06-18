<?php

namespace Modules\Dashboard\Services;


use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PdfGeneratorService
{
    public function generate(string $html): string
    {
        $tempDir = storage_path('app/mpdf-temp');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $this->prepareFonts();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 8,
            'margin_bottom' => 8,

            'tempDir' => $tempDir,

            'fontDir' => array_merge(
                $fontDirs,
                [storage_path('app/fonts')]
            ),

            'fontdata' => array_merge(
                $fontData,
                [
                    'cairo' => [
                        'R'      => 'Cairo-Regular.ttf',
                        'B'      => 'Cairo-Bold.ttf',
                        'useOTL' => 0xFF,
                    ]
                ]
            ),

            'default_font' => 'cairo',
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

    private function prepareFonts(): void
    {
        $fontDir = storage_path('app/fonts');

        if (!file_exists($fontDir)) {
            mkdir($fontDir, 0777, true);
        }

        $regularPath = $fontDir . '/Cairo-Regular.ttf';
        $boldPath = $fontDir . '/Cairo-Bold.ttf';

        if (!file_exists($regularPath)) {

            file_put_contents(
                $regularPath,
                file_get_contents(
                    'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Regular.ttf'
                )
            );
        }

        if (!file_exists($boldPath)) {

            file_put_contents(
                $boldPath,
                file_get_contents(
                    'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/assets/Cairo-Bold.ttf'
                )
            );
        }
    }
}

