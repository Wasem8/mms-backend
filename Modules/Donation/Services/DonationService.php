<?php

namespace Modules\Donation\Services;



use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Donation\Models\Donation;
use Modules\Donation\Models\Setting;
use Modules\Donation\Strategies\PaymentStrategyFactory;
use Modules\Donation\Repositories\SettingRepositoryInterface;
use Modules\Donation\Models\Campaign;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueNeed;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Support\Pdf\PdfGeneratorService;
use App\Support\Storage\SupabaseStorageService;


class DonationService
{

    private const RECEIPT_CACHE_TTL = 60 * 60 * 24 * 30;
    private const RATE_CACHE_KEY = 'setting.usd_to_syp_rate';
    private const RATE_CACHE_TTL = 3600;

    public function __construct(
        protected ImageUploadService $imageUploader,
        private readonly SettingRepositoryInterface  $settingRepo,
        private readonly PdfGeneratorService $pdfGenerator,
        private readonly SupabaseStorageService $storage

    ) {}

    public function getByMosque(int $mosqueId, array $filters = [])
    {
        $perPage = isset($filters['per_page']) ? max(1, min(100, (int) $filters['per_page'])) : 10;

        return Donation::where('mosque_id', $mosqueId)
            ->with(['campaign:id,title'])
            ->when($filters['search']  ?? null, fn($q, $v) => $q->where('donor_name', 'like', "%{$v}%"))
            ->when($filters['type']    ?? null, fn($q, $v) => $q->where('donation_type', $v))
            ->when($filters['status']  ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['campaign'] ?? null, fn($q, $v) => $q->where('campaign_id', $v))
            ->latest()
            ->paginate($perPage);
    }

    public function getRecentDonations(int $mosqueId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Donation::where('mosque_id', $mosqueId)
            ->with(['campaign:id,title'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getByUser(int $userId, array $filters = [])
    {
        $perPage = isset($filters['per_page']) ? max(1, min(100, (int) $filters['per_page'])) : 10;

        return Donation::where('user_id', $userId)
            ->with(['campaign:id,title'])
            ->when($filters['search']  ?? null, fn($q, $v) => $q->where('donor_name', 'like', "%{$v}%"))
            ->when($filters['type']    ?? null, fn($q, $v) => $q->where('donation_type', $v))
            ->when($filters['status']  ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['campaign'] ?? null, fn($q, $v) => $q->where('campaign_id', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Super-admin: paginated donations across ALL mosques.
     * Supports the same filters as the mosque report plus `mosque_id` and `city`.
     */
    public function getAllDonations(array $filters = [])
    {
        $perPage = isset($filters['per_page']) ? max(1, min(100, (int) $filters['per_page'])) : 10;

        return $this->reportQuery(null, $filters)->paginate($perPage);
    }

    public function findByReference(string $reference)
    {
        return Donation::with('user')->where('reference', $reference)->firstOrFail();
    }

    public function find(int $id): Donation
    {
        return Donation::findOrFail($id);
    }

    /**
     * Page stats for a specific mosque (legacy, mosque-scoped helper).
     */
    public function getPageStats(int $mosqueId): array
    {
        return $this->computePageStats(
            fn() => Donation::where('mosque_id', $mosqueId),
            fn() => Campaign::where('mosque_id', $mosqueId),
        );
    }

    /**
     * Page stats for an authenticated user — scoped to their own donations.
     * Active campaigns counts only the active campaigns the user has donated to.
     */
    public function getPageStatsForUser(int $userId): array
    {
        return $this->computePageStats(
            fn() => Donation::where('user_id', $userId),
            fn() => Campaign::where('status', 'active')
                ->whereIn('id', function ($q) use ($userId) {
                    $q->select('campaign_id')
                        ->from('donations')
                        ->where('user_id', $userId)
                        ->whereNotNull('campaign_id');
                }),
        );
    }

    /**
     * Page stats for a super-admin — aggregated across ALL mosques.
     */
    public function getPageStatsForAll(): array
    {
        return $this->computePageStats(
            fn() => Donation::query(),
            fn() => Campaign::query(),
        );
    }

    private function computePageStats(callable $donationBase, callable $campaignBase): array
    {
        $now = now();
        $prev = now()->subMonth();

        // ── Helper: monthly donations for a given year/month ─────────────────
        $monthlyQuery = fn(int $year, int $month) => $donationBase()
            ->where('status', 'completed')
            ->where('donation_type', 'cash')
            ->where(fn($q) => $q
                ->where(fn($q1) => $q1->whereYear('completed_at', $year)->whereMonth('completed_at', $month))
                ->orWhere(fn($q2) => $q2->whereNull('completed_at')->whereYear('created_at', $year)->whereMonth('created_at', $month))
            );

        // ── Helper: new donors for a given year/month ────────────────────────
        $donorsQuery = fn(int $year, int $month) => $donationBase()
            ->where('status', 'completed')
            ->where(fn($q) => $q
                ->where(fn($q1) => $q1->whereYear('completed_at', $year)->whereMonth('completed_at', $month))
                ->orWhere(fn($q2) => $q2->whereNull('completed_at')->whereYear('created_at', $year)->whereMonth('created_at', $month))
            )
            ->distinct('donor_name');

        // ── Total all-time ───────────────────────────────────────────────────
        $totalDonations    = $donationBase()->where('status', 'completed')->where('donation_type', 'cash')->sum('base_amount');
        $prevTotalDonations = $donationBase()->where('status', 'completed')->where('donation_type', 'cash')
            ->where('completed_at', '<', $prev->startOfMonth()->toDateTimeString())
            ->sum('base_amount');

        // ── This month / Last month donations ────────────────────────────────
        $thisMonth  = (float) $monthlyQuery($now->year, $now->month)->sum('base_amount');
        $lastMonth  = (float) $monthlyQuery($prev->year, $prev->month)->sum('base_amount');

        // ── New donors ───────────────────────────────────────────────────────
        $thisDonors = (int) $donorsQuery($now->year, $now->month)->count('donor_name');
        $lastDonors = (int) $donorsQuery($prev->year, $prev->month)->count('donor_name');

        // ── Active campaigns ─────────────────────────────────────────────────
        $activeCampaigns  = (int) $campaignBase()->where('status', 'active')->count();
        $prevActive       = (int) $campaignBase()->where('status', 'active')
            ->where('created_at', '<', $now->startOfMonth()->toDateTimeString())
            ->count();

        // ── Growth helpers ───────────────────────────────────────────────────
        $pct = fn($current, $previous) => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100.0 : 0.0);

        return [
            'total_donations'  => ['value' => (float) $totalDonations,  'growth_percent' => $pct($thisMonth, $lastMonth)],
            'monthly_donations' => ['value' => $thisMonth,              'growth_percent' => $pct($thisMonth, $lastMonth)],
            'new_donors'       => ['value' => $thisDonors,              'growth_percent' => $pct($thisDonors, $lastDonors)],
            'active_campaigns' => ['value' => $activeCampaigns,         'change'         => $activeCampaigns - $prevActive],
        ];
    }
    public function getDailySummary(int $mosqueId): array
    {
        // ── Today ────────────────────────────────────────────────────────────
        $baseQuery = fn() => Donation::where('mosque_id', $mosqueId)
            ->where('donation_type', 'cash')
            ->where('status', 'completed');

        $todayRow = $baseQuery()
            ->where(function ($q) {
                $q->whereDate('completed_at', today())
                    ->orWhere(function ($q2) {
                        $q2->whereNull('completed_at')
                            ->whereDate('created_at', today());
                    });
            })
            ->selectRaw('
            COALESCE(SUM(base_amount), 0) AS total,
            COUNT(*)                      AS operations,
            CASE WHEN COUNT(*) > 0
                 THEN ROUND(COALESCE(SUM(base_amount), 0) / COUNT(*), 2)
                 ELSE 0
            END                           AS average
        ')
            ->first();

        // ── Yesterday ────────────────────────────────────────────────────────
        $yesterdayTotal = $baseQuery()
            ->where(function ($q) {
                $q->whereDate('completed_at', today()->subDay())
                    ->orWhere(function ($q2) {
                        $q2->whereNull('completed_at')
                            ->whereDate('created_at', today()->subDay());
                    });
            })
            ->sum('base_amount');

        // ── Percentage change ─────────────────────────────────────────────────
        $todayTotal = (float) ($todayRow->total ?? 0);

        $percentage = match (true) {
            $yesterdayTotal > 0 => round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 1),
            $todayTotal   > 0   => 100.0,
            default             => 0.0,
        };

        return [
            'total_today'      => $todayTotal,
            'operations_count' => (int)   ($todayRow->operations ?? 0),
            'average_donation' => (float) ($todayRow->average    ?? 0),
            'change_percentage' => $percentage,
            'change_direction'  => match (true) {
                $percentage > 0 => 'up',
                $percentage < 0 => 'down',
                default         => 'neutral',
            },
        ];
    }

    public function getMonthlyDistribution(int $mosqueId): array
    {
        $rows = Donation::where('mosque_id', $mosqueId)
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where(
                    fn($q1) => $q1
                        ->whereYear('completed_at',  now()->year)
                        ->whereMonth('completed_at', now()->month)
                )
                    ->orWhere(
                        fn($q2) => $q2
                            ->whereNull('completed_at')
                            ->whereYear('created_at',  now()->year)
                            ->whereMonth('created_at', now()->month)
                    );
            })
            ->selectRaw('donation_type, COALESCE(SUM(base_amount), 0) as total, COUNT(*) as count')
            ->groupBy('donation_type')
            ->get()
            ->keyBy('donation_type');

        return [
            'cash'    => (float) ($rows['cash']->total ?? 0),
            'in_kind' => (int) ($rows['in_kind']->count ?? 0), // count, not sum — in-kind has no monetary value
        ];
    }
    public function create(array $data): array
    {

        if (!empty($data['campaign_id'])) {
            $campaign  = Campaign::lockForUpdate()->findOrFail($data['campaign_id']);
            $remaining = (float) $campaign->target_amount - (float) $campaign->collected_amount;

            if ($remaining <= 0) {
                throw ValidationException::withMessages([
                    'campaign_id' => __('messages.campaign_already_completed'),
                ]);
            }

            if ((float) ($data['amount'] ?? 0) > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => __('messages.exceeds_remaining', [
                        'remaining' => number_format($remaining, 2),
                        'currency'  => $this->resolveCurrency($data['payment_method']),
                    ]),
                ]);
            }
        }
        if (!empty($data['mosque_need_id'])) {
            $mosqueNeed = MosqueNeed::lockForUpdate()->findOrFail($data['mosque_need_id']);
            $remaining  = (float) $mosqueNeed->target_amount - (float) $mosqueNeed->collected_amount;

            if ($remaining <= 0) {
                throw ValidationException::withMessages([
                    'mosque_need_id' => __('messages.mosque_need_already_fulfilled'),
                ]);
            }

            if ((float) ($data['amount'] ?? 0) > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => __('messages.exceeds_remaining', [
                        'remaining' => number_format($remaining, 2),
                        'currency'  => $this->resolveCurrency($data['payment_method']),
                    ]),
                ]);
            }
        }

        $strategy = PaymentStrategyFactory::make($data['payment_method']);
        $result   = $strategy->pay($data);


        $currency     = $this->resolveCurrency($data['payment_method']);
        $exchangeRate = $this->getCurrentExchangeRate($currency);
        $baseAmount   = round((float) ($data['amount'] ?? 0) * $exchangeRate, 2);

        $donation = DB::transaction(function () use ($data, $result, $currency, $exchangeRate, $baseAmount) {

            $donation = Donation::create([
                'reference'                => $result->reference,
                'mosque_id'                => $data['mosque_id'],
                'user_id'                  => $data['user_id'] ?? null,
                'campaign_id'              => $data['campaign_id'] ?? null,
                'mosque_need_id'           => $data['mosque_need_id'] ?? null,
                'donation_type'            => $data['donation_type'],
                'payment_method'           => $data['payment_method'],
                'amount'                   => $data['amount'] ?? null,
                'item_description'         => $data['item_description'] ?? null,
                'donor_name'               => $data['donor_name'] ?? 'فاعل خير',
                'stripe_payment_intent_id' => $result->paymentIntentId ?? null,
                'status'                   => $result->status,


                'currency'      => $currency,
                'exchange_rate' => $exchangeRate,
                'base_amount'   => $baseAmount,
            ]);

            if ($donation->status === 'completed') {
                if ($donation->campaign_id) {
                    $donation->campaign()->increment('collected_amount', $donation->base_amount);
                } elseif ($donation->mosque_need_id) {
                    $donation->mosqueNeed()->increment('collected_amount', $donation->base_amount);
                } else {
                    // For standalone donations, we might want to track total mosque donations
                    // This is optional and depends on your business logic
                    $donation->mosque()->increment('donation_total', $donation->base_amount);
                }
            }

            return $donation;
        });

        return [
            'donation'      => $donation->fresh(),
            'client_secret' => $result->clientSecret ?? null,
        ];
    }
    public function getReport(int $mosqueId, array $filters = []): array
    {
        $donations = $this->reportQuery($mosqueId, $filters)->get();

        return $this->formatReport($donations, false);
    }

    /**
     * Super-admin report across ALL mosques.
     * Supports the same filters plus `mosque_id` and `city`.
     */
    public function getReportForAll(array $filters = []): array
    {
        $donations = $this->reportQuery(null, $filters)->get();

        return $this->formatReport($donations, true);
    }

    private function reportQuery(?int $mosqueId, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return Donation::query()
            ->with(['campaign:id,title', 'mosque:id,name', 'mosqueNeed:id,description'])
            ->when($mosqueId !== null, fn($q) => $q->where('mosque_id', $mosqueId))
            ->when($filters['search'] ?? null, fn($q, $v) => $q->where('donor_name', 'like', "%{$v}%"))
            ->when($filters['type']   ?? null, fn($q, $v) => $q->where('donation_type', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['campaign'] ?? null, fn($q, $v) => $q->where('campaign_id', $v))
            ->when($mosqueId === null && ($filters['mosque_id'] ?? null), fn($q, $v) => $q->where('mosque_id', $v))
            ->when($filters['city'] ?? null, fn($q, $v) => $q->whereHas('mosque', fn($q2) => $q2->where('city', 'like', "%{$v}%")))
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to']   ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest();
    }

    private function formatReport(\Illuminate\Database\Eloquent\Collection $donations, bool $includeMosque): array
    {
        $completed = $donations->where('status', 'completed');

        return [
            'summary' => [
                'total_amount'  => (float) $completed->sum('base_amount'),
                'total_count'   => $donations->count(),
                'cash_amount'   => (float) $completed->where('donation_type', 'cash')->sum('base_amount'),
                'in_kind_count' => $donations->where('donation_type', 'in_kind')->count(),
                'currency'      => 'SYP',
            ],
            'donations' => $donations->map(fn(Donation $d) => [
                'id'            => $d->id,
                'reference'     => $d->reference,
                'mosque_id'     => $d->mosque_id,
                'mosque_name'   => $includeMosque ? ($d->mosque?->name ?? '—') : null,
                'donor_name'    => $d->donor_name,
                'donation_type' => $d->donation_type,
                'payment_method'=> $d->payment_method,
                'amount'        => $d->amount,
                'base_amount'   => $d->base_amount,
                'currency'      => $d->currency,
                'status'        => $d->status,
                'campaign'      => $d->campaign?->title,
                'created_at'    => $d->created_at,
            ])->all(),
        ];
    }

    public function exportReport(int $mosqueId, array $filters = []): string
    {
        $report = $this->getReport($mosqueId, $filters);
        $mosque = Mosque::find($mosqueId);

        return $this->renderAndUploadReport(
            $report,
            $mosque?->name ?? '—',
            false,
            "donation_report_{$mosqueId}"
        );
    }

    /**
     * Super-admin PDF export across ALL mosques.
     */
    public function exportReportForAll(array $filters = []): string
    {
        $report = $this->getReportForAll($filters);

        return $this->renderAndUploadReport(
            $report,
            'كل المساجد',
            true,
            'donation_report_all'
        );
    }

    private function renderAndUploadReport(array $report, string $mosqueName, bool $showMosque, string $cacheKey): string
    {
        $html = view('donation::reports.donation', [
            'mosque_name'  => $mosqueName,
            'generated_at' => now()->format('Y-m-d H:i'),
            'summary'      => $report['summary'],
            'donations'    => $report['donations'],
            'filters'      => [],
            'showMosque'   => $showMosque,
        ])->render();

        $pdfContent = $this->pdfGenerator->generate($html, $cacheKey, 'cairo');

        $bucket   = config('services.supabase.bucket');
        $fileName = $cacheKey . '_' . now()->timestamp . '.pdf';

        $this->storage->uploadPdf($pdfContent, $fileName, $bucket, true);

        return $this->storage->createSignedUrl($fileName, $bucket);
    }

    public function getReceiptDownloadUrl(Donation $donation): string
    {
        $bucket = config('services.supabase.bucket');
        $base   = "receipt_donation_{$donation->id}";

        // عدّ الإيصالات الموجودة فعلاً لهذا التبرع (القديم بلا رقم + المرقّمة)
        $objects = $this->storage->listObjects($bucket, $base);
        $count   = 0;
        foreach ($objects as $object) {
            $name = $object['name'] ?? null;
            if ($name !== null
                && str_ends_with($name, '.pdf')
                && (str_starts_with($name, "{$base}_") || $name === "{$base}.pdf")
            ) {
                $count++;
            }
        }

        // الحد الأقصى: إيصالان لتبرع واحد
        if ($count >= 2) {
            throw new ConflictHttpException(__('messages.max_receipts_reached'));
        }

        $index    = $count + 1;
        $fileName = "{$base}_{$index}.pdf";

        $donationData = Donation::with(['mosque', 'campaign', 'mosqueNeed'])->findOrFail($donation->id);
        $target = $this->resolveTarget($donationData);

        $html = view('donation::receipts.donation', [
            'donation'        => $donationData,
            'mosque'          => $donationData->mosque,
            'mosque_name'     => $donationData->mosque?->name ?? 'المسجد الرئيسي',
            'target'          => $target,
            'donor_name'      => $donationData->donor_name ?? 'متبرع كريم',
            'payment_method'  => $donationData->payment_method === 'cash' ? 'نقدي' : $donationData->payment_method,
            'donation_status' => $donationData->status === 'completed' ? 'مكتمل' : $donationData->status,
            'currency'        => $donationData->currency ?? 'ليرة سورية',
            'issued_at'       => now()->format('Y-m-d'),
        ])->render();

        $pdfContent = $this->pdfGenerator->generate(
            $html,
            "donation_receipt_{$donation->id}_{$index}",
            'cairo'
        );

        $this->storage->uploadPdf($pdfContent, $fileName, $bucket, true);

        return $this->storage->createSignedUrl($fileName, $bucket);
    }

    private function resolveTarget(Donation $donation): array
    {
        if ($donation->campaign_id) {
            $campaign = $donation->campaign ?? Campaign::find($donation->campaign_id);
            if ($campaign) {
                return ['label' => 'حملة', 'name' => $campaign->title];
            }
        }

        if ($donation->mosque_need_id) {
            $need = $donation->mosqueNeed ?? MosqueNeed::find($donation->mosque_need_id);
            if ($need) {
                return ['label' => 'احتياج', 'name' => $need->description];
            }
        }

        return [
            'label' => 'المسجد',
            'name'  => $donation->mosque?->name ?? 'المسجد الرئيسي',
        ];
    }

    private function resolveCurrency(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'stripe' => 'USD',
            'cash'   => 'SYP',
            default  => 'SYP',
        };
    }
    private function getCurrentExchangeRate(string $currency): float
    {
        if ($currency === 'SYP') {
            return 1.0;
        }

        return Cache::remember(
            self::RATE_CACHE_KEY,
            self::RATE_CACHE_TTL,
            function () {
                $rate = (float) Setting::get('usd_to_syp_rate', 0);

                if ($rate <= 0) {
                    throw new \RuntimeException(
                        'USD → SYP exchange rate is not configured. ' .
                            'Please set it from the Admin Dashboard.'
                    );
                }

                return $rate;
            }
        );
    }
    public function update(int $id, array $data): Donation
    {
        $donation = $this->find($id);

        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            if ($donation->attachment) {
                $this->imageUploader->delete($donation->attachment);
            }
            $data['attachment'] = $this->imageUploader->upload($data['attachment']);
        }

        $donation->update($data);

        return $donation->fresh();
    }

    public function delete(int $id): bool
    {
        $donation = $this->find($id);

        if ($donation->attachment) {
            $this->imageUploader->delete($donation->attachment);
        }

        if ($donation->campaign_id && $donation->status === 'completed') {
            $donation->campaign?->decrement('collected_amount', $donation->amount);
        }

        return $donation->delete();
    }

    public function markCompleted(Donation $donation): void
    {
        if ($donation->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($donation) {
            $donation->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            $this->incrementTotals($donation);
        });
    }

    private function incrementTotals(Donation $donation): void
    {
        $baseAmount = (float) $donation->base_amount;

        if ($baseAmount <= 0) {
            throw new \RuntimeException("base_amount is zero or null on donation #{$donation->id}.");
        }

        if ($donation->campaign_id) {
            $donation->campaign()->increment('collected_amount', $baseAmount);
        } elseif ($donation->mosque_need_id) {
            $donation->mosqueNeed()->increment('collected_amount', $baseAmount);
        }
    }

}
