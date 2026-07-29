<?php

namespace Modules\Donation\ApiResource;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        $collected = (float) $this->collected_amount;
        $target    = (float) $this->target_amount;

        $remainingDays = $this->end_date
            ? max(0, now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($this->end_date)->startOfDay(), false))
            : null;

        return [
            'id'               => $this->id,
            'mosque_id'        => $this->mosque_id,
            'title'            => $this->title,
            'target_amount'    => $target,
            'collected_amount' => $collected,
            'percentage'       => $target > 0 ? round(($collected / $target) * 100, 1) . '%' : '0%',
            'status'           => $this->status,
            'start_date'       => $this->start_date,
            'end_date'         => $this->end_date,
            'cover_image'      => $this->cover_image,
            'remaining_days'   => $remainingDays,
            'donors_count'     => $this->donations()->where('status', 'completed')->distinct('donor_name')->count('donor_name'),
            'mosque'           => $this->whenLoaded('mosque', fn() => [
                'id'        => $this->mosque->id,
                'name'      => $this->mosque->name,
                'city'      => $this->mosque->city,
                'image_url' => $this->mosque->image_url,
            ]),
        ];
    }
}
