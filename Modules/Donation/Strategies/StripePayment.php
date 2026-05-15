<?php

namespace Modules\Donation\Strategies;

use Illuminate\Support\Facades\Log;

class StripePayment implements PaymentStrategyInterface
{
    protected \Stripe\StripeClient $stripe;

    public function __construct(\Stripe\StripeClient $stripe)
    {
        $this->stripe = $stripe;
    }

    public function pay(float $amount, array $details): array
    {
        if ($amount <= 0) {
            return [
                'success'        => false,
                'transaction_id' => '',
                'gateway'        => 'stripe',
                'message'        => 'المبلغ غير صالح للدفع.',
            ];
        }

        if (!$this->validate($details)) {
            return [
                'success'        => false,
                'transaction_id' => '',
                'gateway'        => 'stripe',
                'message'        => 'تفاصيل الدفع غير مكتملة أو غير صحيحة.',
            ];
        }

        try {
            $currency = strtolower($details['currency'] ?? 'sar');
            $amountCents = (int) round($amount * 100);

            $sessionData = [
                'mode'                 => 'payment',
                'currency'             => $currency,
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => $currency,
                        'unit_amount'  => $amountCents,
                        'product_data' => [
                            'name'        => $details['item_description'] ?? $details['description'] ?? 'تبرع',
                            'description' => 'المرجع: ' . ($details['reference'] ?? $details['donation_ref'] ?? ''),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'payment_method_types' => config('services.stripe.payment_methods', ['card']),
                'success_url'          => $details['success_url'],
                'cancel_url'           => $details['cancel_url'],
                'metadata'             => array_merge(
                    ['donation_ref' => $details['donation_ref'] ?? ''],
                    $details['metadata'] ?? []
                ),
            ];

            if (!empty($details['customer_email'])) {
                $sessionData['customer_email'] = $details['customer_email'];
            }

            if (!empty($details['customer_id'])) {
                $sessionData['customer'] = $details['customer_id'];
            }

            $session = $this->stripe->checkout->sessions->create($sessionData);

            return [
                'success'        => true,
                'transaction_id' => $session->id,
                'gateway'        => 'stripe',
                'message'        => 'تم إنشاء جلسة الدفع',
                'checkout_url'   => $session->url,
                'raw'            => $session->toArray(),
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('StripePayment::pay failed', [
                'error'   => $e->getMessage(),
                'details' => $details,
            ]);

            return [
                'success'        => false,
                'transaction_id' => '',
                'gateway'        => 'stripe',
                'message'        => 'فشل الاتصال ببوابة Stripe: ' . $e->getMessage(),
            ];
        }
    }

    public function validate(array $details): bool
    {
        if (empty($details['success_url']) || empty($details['cancel_url'])) {
            return false;
        }

        if (empty($details['item_description']) && empty($details['description']) && empty($details['reference']) && empty($details['donation_ref'])) {
            return false;
        }

        if (!empty($details['customer_email']) && !filter_var($details['customer_email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!empty($details['currency']) && (!is_string($details['currency']) || strlen($details['currency']) !== 3)) {
            return false;
        }

        return true;
    }
}
