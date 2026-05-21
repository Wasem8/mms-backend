<?php

namespace Modules\Donation\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ── Core ──────────────────────────────────────────────────────
            'id'               => $this->id,
            'reference'        => $this->reference,               // REC-4892-2024
            'mosque_id'        => $this->mosque_id,

            // ── Type & payment ────────────────────────────────────────────
            'donation_type'    => $this->donation_type,           // cash | in_kind
            'payment_method'   => $this->payment_method,          // cash | stripe

            // ── Amounts ───────────────────────────────────────────────────
            'amount'           => (float) $this->amount,
            'item_description' => $this->item_description,

            // ── Donor ─────────────────────────────────────────────────────
            'donor_name'       => $this->donor_name,
           
            // ── Relations ─────────────────────────────────────────────────
            'campaign_id'      => $this->campaign_id,
            'campaign_title'   => $this->campaign?->title,
            'mosque_need_id'   => $this->mosque_need_id,

            // ── Attachment (Screen 2) ─────────────────────────────────────
            'attachment'       => $this->attachment,

            // ── Lifecycle ─────────────────────────────────────────────────
            'status'           => $this->status,                  // pending | completed
            'created_at'       => $this->created_at->toDateTimeString(),
            'updated_at'       => $this->updated_at->toDateTimeString(),

            // ── Stripe (only present for online donations) ────────────────
            'client_secret'    => $this->when(
                $this->payment_method === 'stripe' && $this->status === 'pending',
                fn() => null  // populated from service result on create, not stored
            ),
        ];
    }
}
