<?php

namespace Modules\Dashboard\Services;

use Modules\Dashboard\Models\Report;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;
use Modules\Dashboard\Services\MosqueDashboardService;

class MosqueManagerReportService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage,
        private MosqueDashboardService $dashboardService
    ) {}

    public function generate(User $user): array
    {
        $mosqueId = $user->mosque_id;
        $userId = $user->id;

        $cacheType = "mosque_manager_dashboard_user_{$userId}_mosque_{$mosqueId}";

        $lastReport = Report::where('type', $cacheType)->latest()->first();

        if ($lastReport && $lastReport->created_at->gt(now()->subDay())) {
            try {
                return [
                    'url'    => $this->storage->createSignedUrl($lastReport->storage_path),
                    'cached' => true,
                ];
            } catch (\Throwable $e) {
                $lastReport->delete();
            }
        }

        $data = $this->dashboardService->getDashboardData($user);
        $stats = $this->dashboardService->getMosqueStatistics($user);
        $mosqueName = Mosque::find($mosqueId)?->name ?? 'المسجد';

        $html = view('dashboard::reports.mosque_manager', [
            'data'        => $data,
            'stats'       => $stats,
            'mosque_name' => $mosqueName,
            'user_name'   => $user->name,
            'user_role'   => 'مدير المسجد',
            'generated_at' => now()->format('Y-m-d H:i'),
        ])->render();

        $pdfContent = $this->pdfGenerator->generate($html);

        // مسار مسطّح (مجلد واحد) لتفادي مشاكل المسارات المتداخلة عند توقيع الرابط في Supabase
        $filePrefix = "mosque-manager-reports/{$mosqueId}_user_{$userId}";

        [$fileName, $signedUrl] = $this->uploadAndSign($pdfContent, $filePrefix);

        Report::create([
            'user_id'      => $userId,
            'type'         => $cacheType,
            'storage_path' => $fileName,
        ]);

        return [
            'url'    => $signedUrl,
            'cached' => false,
        ];
    }

    /**
     * رفع ملف الـ PDF إلى التخزين السحابي ثم إنشاء رابط موقّع،
     * مع إعادة المحاولة في حال فشل الرفع أو إرجاع السيرفر خطأ "العنصر غير موجود" (NoSuchKey).
     */
    private function uploadAndSign(string $pdfContent, string $filePrefix): array
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $fileName = $filePrefix . '_' . time() . '_' . $attempt . '.pdf';
            try {
                $this->storage->uploadPdf($pdfContent, $fileName);
                return [$fileName, $this->storage->createSignedUrl($fileName)];
            } catch (\Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new \Exception('Failed to generate mosque manager report PDF.');
    }
}
