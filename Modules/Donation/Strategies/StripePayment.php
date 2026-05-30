<?php

namespace Modules\Donation\Strategies;

use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;


class StripePayment implements PaymentStrategyInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function pay(array $data): PaymentResult
    {

        if (!isset($data['amount']) || (float) $data['amount'] <= 0) {
            throw new \InvalidArgumentException('عفواً، يجب تحديد مبلغ مالي صحيح لإتمام عملية الدفع عبر Stripe.');
        }
        $reference = $this->generateReference();

        $intent = $this->stripe->paymentIntents->create([
            'amount'   => $this->toStripeAmount($data['amount']),
            'currency' => config('services.stripe.currency', 'usd'),
            'metadata' => [
                'reference'   => $reference,
                'mosque_id'   => $data['mosque_id'],
                'campaign_id' => $data['campaign_id'] ?? null,
                'donor_name'  => $data['donor_name']  ?? 'فاعل خير',
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return PaymentResult::stripe($reference, $intent->client_secret, $intent->id);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Stripe amounts are in the smallest currency unit (cents for USD).
     * 100.00 USD → 10000 cents
     */
    private function toStripeAmount(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function generateReference(): string
    {
        $sequence  = str_pad(random_int(1000, 9999), 6, '0', STR_PAD_LEFT);
        $year      = now()->year;
        $candidate = "REC-{$sequence}-{$year}";

        while (\Modules\Donation\Models\Donation::where('reference', $candidate)->exists()) {
            $sequence  = str_pad(random_int(1000, 9999), 6, '0', STR_PAD_LEFT);
            $candidate = "REC-{$sequence}-{$year}";
        }

        return $candidate;
    }
}
