<?php

namespace Modules\Dashboard\Services;

use App\Services\SuperAdminActivityService;
use Modules\User\Models\User;
use Modules\Mosque\Models\Mosque;
use Modules\Donation\Models\Campaign;
use Modules\Donation\Models\Donation;
use Modules\Donation\Models\Setting;
use Modules\Complaint\Models\Complaint;
use Modules\Community\Models\Sermon;
use Modules\MaintenanceRequest\Models\Maintenance;

class AdminDashboardService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
        private SupabaseStorageService $storage,
        private SuperAdminActivityService $activityService
    ) {}

    /**
     * بيانات لوحة تحكم المدير (super_admin) على مستوى النظام ككل.
     * البطاقات: مساجد المنطقة، الخطب المعلقة، تبرعات المساجد خلال الشهر، الشكاوى الحرجة.
     */
    public function getDashboardData(): array
    {
        $now = now();

        $donationsThisMonth = Donation::whereNotNull('mosque_id')
            ->whereIn('status', ['paid', 'completed', 'approved'])
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month);

        return [
            'mosques_of_region' => Mosque::count(),
            'mosques_under_maintenance' => Maintenance::whereNotIn('status', ['completed', 'cancelled'])
                ->distinct('mosque_id')
                ->count(),
            'pending_sermons'   => Sermon::where('status', 'Pending')->count(),
            'region_donations_this_month' => [
                'count'             => (clone $donationsThisMonth)->count(),
                'total_base_amount' => (float) (clone $donationsThisMonth)->sum('base_amount'),
                'total_amount'      => (float) (clone $donationsThisMonth)->sum('amount'),
                'currency'          => 'SYP',
                'active_campaigns'  => Campaign::where('status', 'active')->count(),
            ],
            'critical_complaints' => Complaint::where('priority', 'high')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
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
