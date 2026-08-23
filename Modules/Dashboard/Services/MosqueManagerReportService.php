<?php

namespace Modules\Dashboard\Services;

use Modules\Dashboard\Models\Report;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;
use Modules\Dashboard\Services\MosqueDashboardService;
use Modules\Common\Recommendations\RecommendationEngineService;
use App\Support\Storage\SupabaseStorageService;

class MosqueManagerReportService
{
    private const BUCKET = 'images';

    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage,
        private MosqueDashboardService $dashboardService,
        private RecommendationEngineService $recommendationEngine
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
                    'url'    => $this->storage->createSignedUrl($lastReport->storage_path, self::BUCKET),
                    'cached' => true,
                ];
            } catch (\Throwable $e) {
                $lastReport->delete();
            }
        }

        $data = $this->dashboardService->getDashboardData($user);
        $stats = $this->dashboardService->getMosqueStatistics($user);
        $mosqueName = Mosque::find($mosqueId)?->name ?? 'المسجد';

        $recommendations = collect($this->recommendationEngine->generate($mosqueId))
            ->map(fn($dto) => $dto->toArray())
            ->all();

        $html = view('dashboard::reports.mosque_manager', [
            'data'           => $data,
            'stats'          => $stats,
            'mosque_name'    => $mosqueName,
            'user_name'      => $user->name,
            'user_role'      => 'مدير المسجد',
            'generated_at'   => now()->format('Y-m-d H:i'),
            'recommendations' => $recommendations,
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
                $this->storage->uploadPdf($pdfContent, $fileName, self::BUCKET, true);

                // 🔍 تشخيص مؤقت — احذفه بعد حل المشكلة
                $stored = $this->storage->listObjects(self::BUCKET, $filePrefix);
                \Illuminate\Support\Facades\Log::info('Supabase stored objects', [
                    'expected_fileName' => $fileName,
                    'listObjects_result' => $stored,
                ]);

                return [$fileName, $this->storage->createSignedUrl($fileName, self::BUCKET)];
            } catch (\Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new \Exception('Failed to generate mosque manager report PDF.');
    }
}
