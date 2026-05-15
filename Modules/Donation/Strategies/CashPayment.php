<?php

namespace Modules\Donation\Strategies;

class CashPayment implements PaymentStrategyInterface
{
    public function pay(float $amount, array $details): array
    {
        if ($amount <= 0) {
            return [
                'success'        => false,
                'transaction_id' => '',
                'gateway'        => 'cash',
                'message'        => 'المبلغ غير صالح للتبرع النقدي.',
            ];
        }

        if (!$this->validate($details)) {
            return [
                'success'        => false,
                'transaction_id' => '',
                'gateway'        => 'cash',
                'message'        => 'تفاصيل التبرع النقدي غير مكتملة.',
            ];
        }

        $transactionId = uniqid('cash_', true);

        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'gateway'        => 'cash',
            'message'        => 'تم تسجيل التبرع النقدي يدويًا.',
            'raw'            => [
                'type'         => $details['type'] ?? 'cash',
                'reference'    => $details['reference'] ?? null,
                'donor_name'   => $details['donor_name'] ?? null,
                'notes'        => $details['notes'] ?? null,
            ],
        ];
    }

    public function validate(array $details): bool
    {
        if (!empty($details['type']) && $details['type'] !== 'cash') {
            return false;
        }

        if (!empty($details['donor_name']) && !is_string($details['donor_name'])) {
            return false;
        }

        if (!empty($details['reference']) && !is_string($details['reference'])) {
            return false;
        }

        return true;
    }
}
