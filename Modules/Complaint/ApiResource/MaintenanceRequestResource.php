<?php

namespace Modules\Complaint\ApiResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference_number' => $this->reference_number,
            'mosque_id'        => $this->mosque_id,
            'title'            => $this->title,
            'description'      => $this->description,
            'category'         => $this->category,
            'urgency'          => $this->urgency,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'attachments'      => $this->attachments ?? [],
            'created_at'       => $this->created_at->toIso8601String(),
            'updated_at'       => $this->updated_at->toIso8601String(),

            // Only included when the mosque relationship is eager-loaded
            'mosque' => $this->whenLoaded('mosque', fn() => [
                'id'   => $this->mosque->id,
                'name' => $this->mosque->name,
            ]),
        ];
    }
}
