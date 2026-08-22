<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Dashboard\Services\PdfGeneratorService;
use Modules\Dashboard\Services\ReportsService;
use Modules\Dashboard\Services\SupabaseStorageService;
use Modules\User\Models\User;

class ReportController extends Controller
{
    private const TITLES = [
        'donations'   => 'تقرير التبرعات',
        'maintenance' => 'تقرير الصيانة',
        'complaints'  => 'تقرير الشكاوى',
    ];

    private const COLUMNS = [
        'donations'   => ['#', 'المسجد', 'المبلغ', 'العملة', 'بالليرة', 'الحالة', 'التاريخ'],
        'maintenance' => ['#', 'المسجد', 'العنوان', 'التصنيف', 'الأولوية', 'الحالة', 'التاريخ'],
        'complaints'  => ['#', 'الرقم', 'المسجد', 'العنوان', 'الأولوية', 'الحالة', 'التاريخ'],
    ];

    public function __construct(
        protected ReportsService $service,
        protected PdfGeneratorService $pdf,
        protected SupabaseStorageService $storage
    ) {}

    public function donations(Request $request)
    {
        return $this->respond('donations', $request);
    }

    public function maintenance(Request $request)
    {
        return $this->respond('maintenance', $request);
    }

    public function complaints(Request $request)
    {
        return $this->respond('complaints', $request);
    }

    private function respond(string $slug, Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'mosque_id', 'per_page']);
        $result  = match ($slug) {
            'donations'   => $this->service->donationsReport($request->user(), $filters),
            'maintenance' => $this->service->maintenanceReport($request->user(), $filters),
            'complaints'  => $this->service->complaintsReport($request->user(), $filters),
        };

        // توليد الـ PDF وتخزينه في Supabase وإعادة رابطه الموقّع مباشرةً
        $downloadUrl = $this->renderPdf($slug, $request);

        return ApiResponse::success(
            [
                'summary'     => $result['summary'],
                'items'       => $result['items']->items(),
                'download_url' => $downloadUrl,
            ],
            'تم جلب ' . self::TITLES[$slug] . ' بنجاح',
            $result['items']
        );
    }

    public function downloadDonations(Request $request)
    {
        return $this->download('donations', $request);
    }

    public function downloadMaintenance(Request $request)
    {
        return $this->download('maintenance', $request);
    }

    public function downloadComplaints(Request $request)
    {
        return $this->download('complaints', $request);
    }

    private function download(string $slug, Request $request)
    {
        $url = $this->renderPdf($slug, $request);

        return ApiResponse::success(
            ['url' => $url, 'cached' => false],
            'تم إنشاء ' . self::TITLES[$slug] . ' بنجاح'
        );
    }

    /**
     * يولّد HTML ← PDF ← يرفعه إلى Supabase ويعيد رابطاً موقّعاً.
     */
    private function renderPdf(string $slug, Request $request): string
    {
        $filters = $request->only(['date_from', 'date_to', 'mosque_id']);
        $user    = $request->user();

        $result = match ($slug) {
            'donations'   => $this->service->donationsExport($user, $filters),
            'maintenance' => $this->service->maintenanceExport($user, $filters),
            'complaints'  => $this->service->complaintsExport($user, $filters),
        };

        $rows = $this->buildRows($slug, $result['items']);

        $html = view('dashboard::reports.export', [
            'title'    => self::TITLES[$slug],
            'summary'  => $result['summary'],
            'columns'  => self::COLUMNS[$slug],
            'rows'     => $rows,
            'filters'  => $filters,
        ])->render();

        $pdf = $this->pdf->generate($html);

        $fileName = "reports/{$slug}/" . now()->format('Y-m-d_H-i-s') . '_' . uniqid() . '.pdf';

        $this->storage->uploadPdf($pdf, $fileName);

        return $this->storage->createSignedUrl($fileName);
    }

    private function buildRows(string $slug, $items): Collection
    {
        return collect($items)->map(function ($d, $i) use ($slug) {
            return match ($slug) {
                'donations' => [
                    $i + 1,
                    $d->mosque?->name,
                    $d->amount,
                    $d->currency,
                    $d->base_amount,
                    $d->status,
                    $d->created_at?->format('Y-m-d'),
                ],
                'maintenance' => [
                    $i + 1,
                    $d->mosque?->name,
                    $d->title,
                    $d->category,
                    $d->priority,
                    $d->status,
                    $d->created_at?->format('Y-m-d'),
                ],
                'complaints' => [
                    $i + 1,
                    $d->complaint_number,
                    $d->mosque?->name,
                    $d->title,
                    $d->priority,
                    $d->status,
                    $d->created_at?->format('Y-m-d'),
                ],
            };
        });
    }
}

