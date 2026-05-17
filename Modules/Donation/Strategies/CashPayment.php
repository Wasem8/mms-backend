<?php

namespace Modules\Donation\Strategies;

use Modules\Donation\Models\Donation;

class CashPayment implements PaymentStrategyInterface
{
    public function pay(array $data): PaymentResult
    {
        $reference = $this->generateReference();

        return PaymentResult::cash($reference);
    }
 

  
    private function generateReference(): string
    {
        $sequence = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $year     = now()->year;

        $candidate = "REC-{$sequence}-{$year}";

        while (\Modules\Donation\Models\Donation::where('reference', $candidate)->exists()) {
            $sequence  = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = "REC-{$sequence}-{$year}";
        }

        return $candidate;
    }
}
