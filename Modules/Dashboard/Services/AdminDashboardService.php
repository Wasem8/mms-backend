<?php

namespace Modules\Dashboard\Services;

use App\Services\SuperAdminActivityService;
use Modules\Dashboard\Models\Report;
use Modules\User\Models\User;
use Modules\Education\Models\Student;
use Modules\Education\Models\Halaqa;
use Modules\Mosque\Models\Mosque;
use Modules\Donation\Models\Donation;
use Modules\Donation\Models\Setting;
use Modules\Complaint\Models\Complaint;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\Community\Models\Sermon;

class AdminDashboardService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage,
        private SuperAdminActivityService $activityService
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

    /**
     * لوحة تحكم مدير المنطقة (super_admin) المركّزة:
     * مساجد المنطقة، الخطب المعلقة، تبرعات المساجد خلال الشهر،
     * الشكاوى الحرجة، سعر الصرف، وسجل العمليات حسب التاريخ.
     */
    public function getSuperAdminDashboard(User $user, array $filters = []): array
    {
        $now = now();

        // 1) مساجد المنطقة (نطاق مدير المنطقة = كامل النظام)
        $regionMosques = Mosque::latest()->limit(10)->get([
            'id', 'name', 'city_id', 'district_id', 'manager_id', 'status', 'donation_total',
        ]);
        $regionMosquesTotal = Mosque::count();

        // 2) الخطب المعلقة
        $pendingSermons = Sermon::with('mosqueManager:id,name')
            ->where('status', 'Pending')
            ->latest()
            ->limit(10)
            ->get();
        $pendingSermonsTotal = Sermon::where('status', 'Pending')->count();

        // 3) تبرعات مساجد المنطقة خلال الشهر الحالي
        $donationsQuery = Donation::whereNotNull('mosque_id')
            ->whereIn('status', ['paid', 'completed', 'approved'])
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month);

        $regionDonationsThisMonth = [
            'count'             => (clone $donationsQuery)->count(),
            'total_base_amount' => (float) (clone $donationsQuery)->sum('base_amount'),
            'total_amount'      => (float) (clone $donationsQuery)->sum('amount'),
            'currency'          => 'SYP',
        ];

        // 4) الشكاوى الحرجة (أولوية عالية ولم تُحل بعد)
        $criticalComplaints = Complaint::with(['mosque:id,name', 'assignedAdmin:id,name'])
            ->where('priority', 'high')
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest()
            ->limit(10)
            ->get();
        $criticalComplaintsTotal = Complaint::where('priority', 'high')
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // 5) سعر الصرف (USD → SYP)
        $exchangeRate = (float) Setting::get('usd_to_syp_rate', 0);

        // 6) سجل العمليات حسب التاريخ
        $activity = $this->activityService->getActivity($user, $filters);

        return [
            'exchange_rate' => $exchangeRate,
            'region_mosques' => [
                'total' => $regionMosquesTotal,
                'items' => $regionMosques,
            ],
            'pending_sermons' => [
                'total' => $pendingSermonsTotal,
                'items' => $pendingSermons,
            ],
            'region_donations_this_month' => $regionDonationsThisMonth,
            'critical_complaints' => [
                'total' => $criticalComplaintsTotal,
                'items' => $criticalComplaints,
            ],
            'activity_log' => [
                'data' => $activity->items(),
                'meta' => [
                    'current_page' => $activity->currentPage(),
                    'last_page'    => $activity->lastPage(),
                    'per_page'     => $activity->perPage(),
                    'total'        => $activity->total(),
                ],
            ],
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
