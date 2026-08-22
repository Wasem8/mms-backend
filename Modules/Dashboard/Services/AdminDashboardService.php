<?php

namespace Modules\Dashboard\Services;

use Modules\Dashboard\Models\Report;
use Modules\User\Models\User;
use Modules\Education\Models\Student;
use Modules\Education\Models\Halaqa;
use Modules\Mosque\Models\Mosque;
use Modules\Donation\Models\Donation;
use Modules\Complaint\Models\Complaint;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\Community\Models\Sermon;

class AdminDashboardService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage
    ) {}

    /**
     * بيانات لوحة تحكم المدير (super_admin) على مستوى النظام ككل.
     */
    public function getDashboardData(): array
    {
        return [
            'totals' => [
                'mosques'     => Mosque::count(),
                'students'    => Student::count(),
                'halaqas'     => Halaqa::count(),
                'teachers'    => User::role('teacher')->count(),
                'volunteers'  => User::role('volunteer')->count(),
                'managers'    => User::role('mosque_manager')->count(),
                'supervisors' => User::role('halaqa_supervisor')->count(),
                'parents'     => User::role('parent')->count(),
            ],
            'donations' => (float) Donation::whereIn('status', ['paid', 'completed', 'approved'])
                ->sum('amount'),
            'complaints' => [
                'total'   => Complaint::count(),
                'pending' => Complaint::where('status', 'pending')->count(),
                'urgent'  => Complaint::where('priority', 'high')->count(),
            ],
            'maintenance' => [
                'total'   => Maintenance::count(),
                'pending' => Maintenance::where('status', 'pending')->count(),
            ],
            'pending_sermons' => Sermon::where('status', 'Pending')->count(),
        ];
    }

    public function generatePdfResponse(User $user): array
    {
        $cacheType = "admin_dashboard_user_{$user->id}";

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

        $data = $this->getDashboardData();

        $html = view('dashboard::reports.admin', [
            'data'         => $data,
            'user_name'    => $user->name,
            'user_role'    => 'مدير المنطقة',
            'generated_at' => now()->format('Y-m-d H:i'),
        ])->render();

        $pdfContent = $this->pdfGenerator->generate($html);

        // مسار مسطّح (مجلد واحد) لتفادي مشاكل المسارات المتداخلة عند توقيع الرابط في Supabase
        $filePrefix = "admin-reports/user_{$user->id}";

        [$fileName, $signedUrl] = $this->uploadAndSign($pdfContent, $filePrefix);

        Report::create([
            'user_id'      => $user->id,
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

        throw $lastException ?? new \Exception('Failed to generate admin report PDF.');
    }
}
