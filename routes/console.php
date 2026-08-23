<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('certificates:regenerate-placeholders', function () {
    $service = app(\Modules\Volunteer\Services\VolunteerEvaluationService::class);
    $regenerated = 0;
    $failed = 0;

    \Modules\Volunteer\Models\VolunteerCertificate::query()
        ->whereNotNull('certificate_url')
        ->chunkById(50, function ($certificates) use ($service, &$regenerated, &$failed) {
            foreach ($certificates as $certificate) {
                // الروابط الوهمية من بيانات الـ seeder (https://placeholder.wasl-mms.test/...)
                // تُعاد توليدها ورفعها إلى Supabase وتُحفظ كاسم ملف حقيقي.
                if (filter_var($certificate->certificate_url, FILTER_VALIDATE_URL)) {
                    try {
                        $service->getCertificateDownloadUrl($certificate);
                        $regenerated++;
                        $this->info("✓ Regenerated certificate #{$certificate->id} (opp {$certificate->opportunity_id})");
                    } catch (\Throwable $e) {
                        $failed++;
                        $this->error("✗ Failed certificate #{$certificate->id}: {$e->getMessage()}");
                    }
                }
            }
        });

    $this->info("Done. Regenerated: {$regenerated}, Failed: {$failed}");
})->purpose('Regenerate placeholder (seeder) certificate files in Supabase');

Schedule::command('sermons:purge-expired')->dailyAt('00:01');
