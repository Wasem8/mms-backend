<?php

namespace Modules\Donation\Strategies;

class PaymentProcessor
{
    private PaymentStrategyInterface $paymentStrategy;

    public function setPaymentStrategy(PaymentStrategyInterface $paymentStrategy): void
    {
        $this->paymentStrategy = $paymentStrategy;
    }

    public function processPayment(float $amount, array $details): array
    {
        return $this->paymentStrategy->pay($amount, $details);
    }
}
