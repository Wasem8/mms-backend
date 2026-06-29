<?php

namespace Modules\Donation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Donation\Models\Donation;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use Modules\Donation\Services\DonationService;

class StripeWebhookController extends Controller
{

    public function __construct(
        private readonly DonationService $donationService,
    ) {}

    public function handle(Request $request)
    {
        $event = $this->parseStripeEvent($request);

        if (! $event) {
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded'      => $this->handleSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handleFailed($event->data->object), // تمت إضافة الاستدعاء هنا
            default                         => null,
        };

        return response()->json(['status' => 'ok']);
    }


    // ─── Private ─────────────────────────────────────────────────────────────

    private function handleSucceeded(object $paymentIntent): void
    {
        $donation = Donation::where('stripe_payment_intent_id', $paymentIntent->id)
            ->where('status', 'pending')
            ->first();

        if (! $donation) {
            Log::warning('StripeWebhook: no pending donation found for payment_intent', [
                'payment_intent_id' => $paymentIntent->id,
            ]);
            return;
        }

        $this->donationService->markCompleted($donation);

        Log::info('StripeWebhook: donation completed via base_amount', [
            'donation_id'      => $donation->id,
            'currency'         => $donation->currency,
            'amount'           => $donation->amount,
            'exchange_rate'    => $donation->exchange_rate,
            'base_amount_syp'  => $donation->base_amount,
            'campaign_id'      => $donation->campaign_id,
        ]);
    }
    private function handleFailed(object $intent): void
    {
        Log::warning('Stripe payment failed', ['intent_id' => $intent->id]);
    }

    private function parseStripeEvent(Request $request): ?\Stripe\Event
    {
        // 🚀 [لبيئة الاختبار فقط] تجاوز فحص التوقيع محلياً لتسهيل الاختبار
        if (app()->environment('local')) {
            $payload = json_decode($request->getContent(), true);
            return \Stripe\Event::constructFrom($payload);
        }

        // 🔒 [في بيئة الإنتاج Production] الكود الآمن الأصلي
        $secret    = config('services.stripe.webhook_secret');
        $signature = $request->header('Stripe-Signature');

        try {
            return Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $secret,
            );
        } catch (SignatureVerificationException $e) {
            Log::error('StripeWebhook: signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
