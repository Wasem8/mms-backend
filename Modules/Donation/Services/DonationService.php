<?php

namespace Modules\Donation\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Donation\Repositories\DonationRepositoryInterface;
use Modules\Donation\Strategies\CashPayment;
use Modules\Donation\Strategies\PaymentProcessor;
use Modules\Donation\Strategies\StripePayment;

class DonationService
{
    protected DonationRepositoryInterface $donationRepository;

    public function __construct(DonationRepositoryInterface $donationRepository)
    {
        $this->donationRepository = $donationRepository;
    }

    public function getAllDonations()
    {
        return $this->donationRepository->all();
    }

    public function getDonationById(int $id)
    {
        return $this->donationRepository->find($id);
    }

    public function createDonation(array $data)
    {
        $data = $this->normalizeDonationData($data);

        return $this->donationRepository->create($data);
    }

    public function createDonationWithPayment(array $data)
    {
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $processor = new PaymentProcessor();

        if ($paymentMethod === 'stripe') {
            $processor->setPaymentStrategy(new StripePayment(
                new \Stripe\StripeClient(config('services.stripe.secret'))
            ));
        } else {
            $processor->setPaymentStrategy(new CashPayment());
        }

        $paymentResult = $processor->processPayment($data['amount'], $data);

        if (!isset($paymentResult['success']) || $paymentResult['success'] === false) {
            return ['donation' => null, 'payment' => $paymentResult];
        }

        if ($paymentMethod === 'stripe') {
            $data['status'] = 'pending';
        } else {
            $data['status'] = 'completed';
        }

        $donation = $this->createDonation($data);

        return ['donation' => $donation, 'payment' => $paymentResult];
    }

    public function updateDonation(int $id, array $data)
    {
        if (isset($data['status']) && $data['status'] === 'completed' && empty($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        return $this->donationRepository->update($id, $data);
    }

    public function deleteDonation(int $id): void
    {
        $this->donationRepository->delete($id);
    }

    private function normalizeDonationData(array $data): array
    {
        if (empty($data['reference'])) {
            $data['reference'] = $this->generateReference();
        }

        if (!isset($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        if (!isset($data['status'])) {
            if (($data['type'] ?? '') === 'kind') {
                $data['status'] = 'pending';
            } elseif (isset($data['payment_method']) && $data['payment_method'] === 'stripe' && ($data['type'] ?? '') === 'cash') {
                $data['status'] = 'pending';
            } else {
                $data['status'] = 'completed';
            }

            if ($data['status'] === 'completed') {
                $data['completed_at'] = now();
            }
        } elseif ($data['status'] === 'completed' && empty($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        return $data;
    }

    private function generateReference(): string
    {
        return strtoupper('DON-' . substr(Str::uuid()->toString(), 0, 8));
    }
}
