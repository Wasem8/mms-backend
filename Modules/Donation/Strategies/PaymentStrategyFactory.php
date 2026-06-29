<?php

namespace Modules\Donation\Strategies;

use InvalidArgumentException;

class PaymentStrategyFactory
{
    /**
     * Resolve the correct strategy from the payment_method value.
     *
     * Supported values (match the donations.payment_method enum):
     *   'cash'   → CashPaymentStrategy   (covers: cash, bank_transfer, cheque)
     *   'stripe' → StripePaymentStrategy  (online card payments)
     */
    public static function make(string $paymentMethod): PaymentStrategyInterface
    {
       return match($paymentMethod) {
            'cash' => new CashPayment(),
            'stripe' => new StripePayment(),
            default => throw new InvalidArgumentException("Unsupported payment method: {$paymentMethod}"),
        };
    }
}
