<?php

namespace Modules\Dashboard\Services;

use Modules\Dashboard\Models\Report;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class MosqueReportService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage
    ) {}

    /**
     * 1) التقرير الإحصائي الشامل لمساجد المنطقة (super_admin فقط).
     */
    public function regionalCensus(User $user): array
    {
        $mosques = Mosque::with(['city', 'district', 'manager', 'facilities', 'spaces', 'needs'])
            ->get();

        $byCity = $mosques
            ->groupBy(fn($m) => $m->city?->name_ar ?? 'غير محدد')
            ->map(fn($items, $city) => [
                'city'          => $city,
                'count'         => $items->count(),
                'capacity'      => $items->sum(fn($m) => $m->spaces->sum('capacity')),
                'active'        => $items->where('status', 'active')->count(),
                'maintenance'   => $items->where('status', 'maintenance')->count(),
                'construction'  => $items->where('status', 'construction')->count(),
            ])
            ->values();

        $data = [
            'total_mosques'  => $mosques->count(),
            'total_capacity' => $mosques->sum(fn($m) => $m->spaces->sum('capacity')),
            'by_city'        => $byCity,
            'mosques'        => $mosques->map(function ($m) {
                return [
                    'name'        => $m->name,
                    'city'        => $m->city?->name_ar,
                    'district'    => $m->district?->name_ar,
                    'status'      => $m->status,
                    'manager'     => $m->manager?->name,
                    'imam'        => $m->imam,
                    'khatib'      => $m->khatib,
                    'capacity'    => $m->spaces->sum('capacity'),
                    'facilities'  => $m->facilities->pluck('name')->toArray(),
                    'gps'         => $m->latitude && $m->longitude
                        ? "{$m->latitude}, {$m->longitude}"
                        : null,
                ];
            })->toArray(),
        ];

        return $this->buildAndStore(
            "mosque_census_admin_{$user->id}",
            'dashboard::reports.mosque_census',
            $this->viewData($user, 'مدير المنطقة', $data),
            "mosque-reports/census/user_{$user->id}",
            $user->id
        );
    }

    /**
     * 2) تقرير بطاقة المسجد التعريفية (مسجد محدد).
     *    مدير المسجد مقيد بمسجده فقط، ومدير المنطقة يمكنه اختيار أي مسجد.
     */
    public function mosqueFactSheet(User $user, ?int $mosqueId = null): array
    {
        if ($user->hasRole('super_admin')) {
            $mosqueId = $mosqueId ?? $user->mosque_id;
        } else {
            // مدير المسجد مقصور على مسجده
            $mosqueId = $user->mosque_id;
        }

        $mosque = Mosque::with(['city', 'district', 'manager', 'facilities', 'spaces', 'needs'])
            ->findOrFail($mosqueId);

        $data = [
            'mosque' => [
                'name'        => $mosque->name,
                'image_url'   => $mosque->image_url,
                'status'      => $mosque->status,
                'address'     => trim(($mosque->city?->name_ar ?? '') . ' - ' . ($mosque->district?->name_ar ?? '')),
                'gps'         => $mosque->latitude && $mosque->longitude
                    ? "{$mosque->latitude}, {$mosque->longitude}"
                    : null,
                'manager'     => $mosque->manager?->name,
                'imam'        => $mosque->imam,
                'khatib'      => $mosque->khatib,
                'spaces'      => $mosque->spaces->map(fn($s) => [
                    'name'     => $s->name,
                    'capacity' => $s->capacity,
                ])->toArray(),
                'facilities'  => $mosque->facilities->pluck('name')->toArray(),
                'needs'       => $mosque->needs->map(fn($n) => [
                    'title'  => $n->title,
                    'type'   => $n->type,
                    'status' => $n->status,
                ])->toArray(),
            ],
        ];

        $roleTitle = $user->hasRole('super_admin') ? 'مدير المنطقة' : 'مدير المسجد';

        return $this->buildAndStore(
            "mosque_fact_sheet_{$mosqueId}",
            'dashboard::reports.mosque_fact_sheet',
            $this->viewData($user, $roleTitle, $data),
            "mosque-reports/fact-sheet/mosque_{$mosqueId}",
            $user->id
        );
    }

    /**
     * 3) تقرير جاهزية المساجد للمواسم (super_admin فقط) — يخص الجوامع الكبرى.
     */
    public function seasonsReadiness(User $user): array
    {
        $mosques = Mosque::with(['facilities', 'spaces', 'maintenanceRequests'])
            ->where('is_featured', true)
            ->orWhere('status', 'active')
            ->get();

        $data = [
            'mosques' => $mosques->map(function ($m) {
                $openMaintenance = $m->maintenanceRequests
                    ->where('status', 'pending')
                    ->count();

                $readiness = match (true) {
                    $openMaintenance > 0 => 'يحتاج متابعة',
                    default              => 'جاهز',
                };

                return [
                    'name'            => $m->name,
                    'capacity'        => $m->spaces->sum('capacity'),
                    'facilities'      => $m->facilities->pluck('name')->toArray(),
                    'open_maintenance'=> $openMaintenance,
                    'readiness'       => $readiness,
                ];
            })->toArray(),
        ];

        return $this->buildAndStore(
            "mosque_readiness_admin_{$user->id}",
            'dashboard::reports.mosque_readiness',
            $this->viewData($user, 'مدير المنطقة', $data),
            "mosque-reports/readiness/user_{$user->id}",
            $user->id
        );
    }

    /**
     * بناء الـ PDF، رفعه مع توقيع الرابط (مع إعادة محاولة)، وتخزين سجل الكاش.
     */
    private function buildAndStore(
        string $cacheType,
        string $view,
        array $viewData,
        string $filePrefix,
        ?int $userId
    ): array {
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

        $html = view($view, $viewData)->render();
        $pdfContent = $this->pdfGenerator->generate($html);

        // مسار مسطّح (مجلد واحد) لتفادي مشاكل المسارات المتداخلة عند توقيع الرابط
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

    private function viewData(User $user, string $roleTitle, array $data): array
    {
        return array_merge($data, [
            'user_name'    => $user->name,
            'user_role'    => $roleTitle,
            'generated_at' => now()->format('Y-m-d H:i'),
        ]);
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

        throw $lastException ?? new \Exception('Failed to generate mosque report PDF.');
    }
}
