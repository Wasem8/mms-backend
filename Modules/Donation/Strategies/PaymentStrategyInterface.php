<?php

namespace Modules\Donation\Strategies;


Interface PaymentStrategyInterface
{
    public function pay(array $data): PaymentResult;
}

