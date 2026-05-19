<?php

namespace Modules\Donation\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Donation\Models\Donation;
use Modules\Donation\Models\Setting;
use Modules\Donation\Strategies\PaymentStrategyFactory;
use Modules\Donation\Repositories\SettingRepositoryInterface;
use Modules\Donation\Models\Campaign;
use Modules\Mosque\Models\MosqueNeed;
use ArPHP\I18N\Arabic;
use Spatie\Browsershot\Browsershot;

class DonationService
{

    private const RATE_CACHE_KEY = 'setting.usd_to_syp_rate';
    private const RATE_CACHE_TTL = 3600;

    public function __construct(
        protected ImageUploadService $imageUploader,
        private readonly SettingRepositoryInterface  $settingRepo,

    ) {}

    public function getByMosque(int $mosqueId, array $filters = [])
    {
        return Donation::where('mosque_id', $mosqueId)
            ->when($filters['search']  ?? null, fn($q, $v) => $q->where('donor_name', 'like', "%{$v}%"))
            ->when($filters['type']    ?? null, fn($q, $v) => $q->where('donation_type', $v))
            ->when($filters['status']  ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['campaign'] ?? null, fn($q, $v) => $q->where('campaign_id', $v))
            ->latest()
            ->paginate(10);
    }

    public function getByUser(int $userId, array $filters = [])
    {
        return Donation::where('user_id', $userId)
            ->when($filters['search']  ?? null, fn($q, $v) => $q->where('donor_name', 'like', "%{$v}%"))
            ->when($filters['type']    ?? null, fn($q, $v) => $q->where('donation_type', $v))
            ->when($filters['status']  ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['campaign'] ?? null, fn($q, $v) => $q->where('campaign_id', $v))
            ->latest()
            ->paginate(10);
    }

    public function find(int $id): Donation
    {
        return Donation::findOrFail($id);
    }
    // DonationService.php

    public function getDailySummary(int $mosqueId): array
    {
        $query = Donation::where('mosque_id', $mosqueId)
            ->where('donation_type', 'cash')
            ->where('status', 'completed');


        $query->where(function ($q) {
            $q->whereDate('completed_at', today())
                ->orWhere(function ($q2) {
                    $q2->whereNull('completed_at')
                        ->whereDate('created_at', today());
                });
        });

        $row = $query
            ->selectRaw('COALESCE(SUM(base_amount), 0) as total, COUNT(*) as operations')
            ->first();

        return [
            'total_today'      => (float) ($row->total      ?? 0),
            'operations_count' => (int)   ($row->operations ?? 0),
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
            ->selectRaw('donation_type, COALESCE(SUM(base_amount), 0) as total')
            ->groupBy('donation_type')
            ->pluck('total', 'donation_type');

        return [
            'cash'    => (float) ($rows['cash']    ?? 0),
            'in_kind' => (float) ($rows['in_kind'] ?? 0),
        ];
    }
    public function create(array $data): array
    {
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
    public function generateReceipt(Donation $donation): string
    {

        $donationData = Donation::with(['mosque', 'campaign', 'mosqueNeed'])->findOrFail($donation->id);
        $target = $this->resolveTarget($donationData);

        $html = view('donation::receipts.donation', [
            'donation'       => $donationData,
            'mosque'         => $donationData->mosque,
            'mosque_name'    => $donationData->mosque?->name ?? 'المسجد الرئيسي',
            'target'         => $target,
            'donor_name'     => $donationData->donor_name ?? 'متبرع كريم', // سيظهر الآن: أويس عبود
            'payment_method' => $donationData->payment_method === 'cash' ? 'نقدي' : $donationData->payment_method,
            'donation_status' => $donationData->status === 'completed' ? 'مكتمل' : $donationData->status,
            'currency'       => $donationData->currency ?? 'ليرة سورية',
            'issued_at'      => now()->format('Y-m-d'),
        ])->render();

        return Browsershot::html($html)
            ->setNodeBinary('C:\\Program Files\\nodejs\\node.exe')
            ->setNpmBinary('C:\\Program Files\\nodejs\\npm.cmd')
            ->noSandbox()
            ->emulateMedia('print')
            ->preferCssPageSize()
            ->scale(1.0)
            ->margins(0, 0, 0, 0)
            ->paperSize(210, 297)
            ->showBackground()
            ->pdf();
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
    private function renderHtml(Donation $donation, array $target): string
    {
        // تأمين جلب البيانات الطازجة مباشرة من قاعدة البيانات بعد الحفظ
        $donation = $donation->fresh(['mosque', 'campaign', 'mosqueNeed']);

        return view('donation::receipts.donation', [
            'donation'       => $donation,
            'mosque'         => $donation->mosque,
            'mosque_name'    => $donation->mosque?->name ?? 'المسجد الرئيسي',
            'target'         => $target,
            // استخدام الاسم القادم من قاعدة البيانات (مثل: أويس عبود) وفي حال عدم وجوده نضع القيمة الافتراضية
            'donor_name'     => $donation->donor_name ?? 'متبرع كريم',
            'payment_method' => $donation->payment_method === 'cash' ? 'نقدي' : $donation->payment_method,
            'donation_status' => $donation->status === 'completed' ? 'مكتمل' : $donation->status,
            'currency'       => $donation->currency ?? 'SYP', // أو اجعلها ديناميكية بناءً على الحقل لديك
            'issued_at'      => now()->format('Y-m-d'),
        ])->render();
    }
}
