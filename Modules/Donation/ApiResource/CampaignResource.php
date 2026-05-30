<?php

namespace Modules\Donation\ApiResource;

use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        $collected = (float) $this->collected_amount;
        $target    = (float) $this->target_amount;

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'target_amount'    => $target,
            'collected_amount' => $collected,
            'percentage' => $target > 0 ? round(($collected / $target) * 100, 1) . '%' : '0%',
            'status'           => $this->status,
            'start_date'       => $this->start_date,
            'end_date'         => $this->end_date,
            'cover_image'      => $this->cover_image,
        ];
    }
}
