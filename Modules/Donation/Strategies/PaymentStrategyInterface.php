<?php

namespace Modules\Donation\Strategies;


Interface PaymentStrategyInterface
{
    public function pay(float $amount, array $details): array;
    public function validate(array $details): bool;
}

