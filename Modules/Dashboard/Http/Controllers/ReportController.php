<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Dashboard\Services\PdfGeneratorService;
use Modules\Dashboard\Services\ReportsService;
use Modules\Dashboard\Services\SupabaseStorageService;

class ReportController extends Controller
{
    private const TITLES = [
        'donations' => 'تقرير التبرعات',
        'maintenance' => 'تقرير الصيانة',
        'complaints' => 'تقرير الشكاوى',
    ];

    private const COLUMNS = [
        'donations' => [
            '#',
            'المسجد',
            'المبلغ',
            'العملة',
            'بالليرة',
            'الحالة',
            'التاريخ',
        ],

        'maintenance' => [
            '#',
            'المسجد',
            'عنوان الطلب',
            'التصنيف',
            'الأولوية',
            'الحالة',
            'تاريخ الطلب',
        ],

        'complaints' => [
            '#',
            'الرقم',
            'المسجد',
            'العنوان',
            'الأولوية',
            'الحالة',
            'التاريخ',
        ],
    ];

    public function __construct(
        protected ReportsService $service,
        protected PdfGeneratorService $pdf,
        protected SupabaseStorageService $storage
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    public function donations(Request $request)
    {
        return $this->respond(
            'donations',
            $request
        );
    }

    public function maintenance(Request $request)
    {
        return $this->respond(
            'maintenance',
            $request
        );
    }

    public function complaints(Request $request)
    {
        return $this->respond(
            'complaints',
            $request
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Main Response
    |--------------------------------------------------------------------------
    */

    private function respond(
        string $slug,
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Create PDF
        |--------------------------------------------------------------------------
        */

        $downloadUrl = $this->renderPdf(
            $slug,
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return ApiResponse::success(
            [
                'download_url' => $downloadUrl,
            ],
            'تم جلب ' . self::TITLES[$slug] . ' بنجاح'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Download Endpoints
    |--------------------------------------------------------------------------
    */

    public function downloadDonations(
        Request $request
    ) {
        return $this->download(
            'donations',
            $request
        );
    }

    public function downloadMaintenance(
        Request $request
    ) {
        return $this->download(
            'maintenance',
            $request
        );
    }

    public function downloadComplaints(
        Request $request
    ) {
        return $this->download(
            'complaints',
            $request
        );
    }

    private function download(
        string $slug,
        Request $request
    ) {
        $url = $this->renderPdf(
            $slug,
            $request
        );

        return ApiResponse::success(
            [
                'url' =>
                    $url,

                'cached' =>
                    false,
            ],
            'تم إنشاء ' .
            self::TITLES[$slug] .
            ' بنجاح'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render PDF
    |--------------------------------------------------------------------------
    */

    private function renderPdf(
        string $slug,
        Request $request
    ): string {
        $filters = $request->only([
            'date_from',
            'date_to',
            'mosque_id',
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Export Data
        |--------------------------------------------------------------------------
        */

        $result = match ($slug) {

            'donations' =>
            $this->service->donationsExport(
                $user,
                $filters
            ),

            'maintenance' =>
            $this->service->maintenanceExport(
                $user,
                $filters
            ),

            'complaints' =>
            $this->service->complaintsExport(
                $user,
                $filters
            ),
        };

        /*
        |--------------------------------------------------------------------------
        | Rows
        |--------------------------------------------------------------------------
        */

        $rows = $this->buildRows(
            $slug,
            $result['items']
        );

        /*
        |--------------------------------------------------------------------------
        | Blade
        |--------------------------------------------------------------------------
        */

        $html = view(
            'dashboard::reports.export',
            [
                'title' =>
                    self::TITLES[$slug],

                'summary' =>
                    $result['summary'],

                'columns' =>
                    self::COLUMNS[$slug],

                'rows' =>
                    $rows,

                'filters' =>
                    $filters,

                'slug' =>
                    $slug,

                'generatedAt' =>
                    now(),

                'user' =>
                    $user,
            ]
        )->render();

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdfContent = $this->pdf->generate(
            $html
        );

        /*
        |--------------------------------------------------------------------------
        | File Name
        |--------------------------------------------------------------------------
        */

        $fileName =
            'reports/' .
            $slug .
            '/' .
            now()->format(
                'Y-m-d_H-i-s'
            ) .
            '_' .
            uniqid() .
            '.pdf';

        /*
        |--------------------------------------------------------------------------
        | Upload to Supabase
        |--------------------------------------------------------------------------
        */

        $this->storage->uploadPdf(
            $pdfContent,
            $fileName
        );

        /*
        |--------------------------------------------------------------------------
        | Signed URL
        |--------------------------------------------------------------------------
        */

        return $this->storage->createSignedUrl(
            $fileName
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Build Rows
    |--------------------------------------------------------------------------
    */

    private function buildRows(
        string $slug,
               $items
    ): Collection {
        return collect($items)
            ->values()
            ->map(
                function ($item, $index) use ($slug) {

                    return match ($slug) {

                        /*
                        |--------------------------------------------------------------------------
                        | Donations
                        |--------------------------------------------------------------------------
                        */

                        'donations' => [
                            $index + 1,

                            $item->mosque?->name
                            ?? '-',

                            number_format(
                                (float) $item->amount,
                                2
                            ),

                            $item->currency
                            ?? 'SYP',

                            number_format(
                                (float) $item->base_amount,
                                2
                            ),

                            $this->translateDonationStatus(
                                $item->status
                            ),

                            $item->created_at?->format(
                                'Y-m-d'
                            ) ?? '-',
                        ],

                        /*
                        |--------------------------------------------------------------------------
                        | Maintenance
                        |--------------------------------------------------------------------------
                        */

                        'maintenance' => [
                            $index + 1,

                            $item->mosque?->name
                            ?? '-',

                            $item->title
                            ?? '-',

                            $item->category
                            ?? '-',

                            $this->translatePriority(
                                $item->priority
                            ),

                            $this->translateMaintenanceStatus(
                                $item->status
                            ),

                            $item->created_at?->format(
                                'Y-m-d'
                            ) ?? '-',
                        ],

                        /*
                        |--------------------------------------------------------------------------
                        | Complaints
                        |--------------------------------------------------------------------------
                        */

                        'complaints' => [
                            $index + 1,

                            $item->complaint_number
                            ?? '-',

                            $item->mosque?->name
                            ?? '-',

                            $item->title
                            ?? '-',

                            $this->translatePriority(
                                $item->priority
                            ),

                            $this->translateComplaintStatus(
                                $item->status
                            ),

                            $item->created_at?->format(
                                'Y-m-d'
                            ) ?? '-',
                        ],
                    };
                }
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Priority Translation
    |--------------------------------------------------------------------------
    */

    private function translatePriority(
        ?string $priority
    ): string {
        return match ($priority) {

            'low' =>
            'منخفضة',

            'medium' =>
            'متوسطة',

            'high' =>
            'عالية',

            'urgent' =>
            'عاجلة',

            default =>
                $priority ?? '-',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Maintenance Status
    |--------------------------------------------------------------------------
    */

    private function translateMaintenanceStatus(
        ?string $status
    ): string {
        return match ($status) {

            'pending' =>
            'قيد الانتظار',

            'in_progress' =>
            'قيد التنفيذ',

            'completed' =>
            'مكتمل',

            'cancelled' =>
            'ملغى',

            default =>
                $status ?? '-',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Complaint Status
    |--------------------------------------------------------------------------
    */

    private function translateComplaintStatus(
        ?string $status
    ): string {
        return match ($status) {

            'pending' =>
            'قيد الانتظار',

            'in_progress' =>
            'قيد المعالجة',

            'resolved' =>
            'تم الحل',

            'closed' =>
            'مغلق',

            'rejected' =>
            'مرفوض',

            default =>
                $status ?? '-',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Donation Status
    |--------------------------------------------------------------------------
    */

    private function translateDonationStatus(
        ?string $status
    ): string {
        return match ($status) {

            'pending' =>
            'قيد الانتظار',

            'paid' =>
            'مدفوع',

            'completed' =>
            'مكتمل',

            'approved' =>
            'معتمد',

            default =>
                $status ?? '-',
        };
    }
}
