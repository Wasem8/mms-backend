<?php

namespace Modules\Donation\Strategies;

readonly class PaymentResult
{
    public function __construct(
        public string  $status,           
        public string  $reference,        
        public ?string $clientSecret,     
        public ?string $paymentIntentId,  
    ) {}

    public static function cash(string $reference): self
    {
        return new self(
            status: 'completed',
            reference: $reference,
            clientSecret: null,
            paymentIntentId: null,
        );
    }

    public static function stripe(string $reference, string $clientSecret, string $intentId): self
    {
        return new self(
            status: 'pending',
            reference: $reference,
            clientSecret: $clientSecret,
            paymentIntentId: $intentId,
        );
    }
}