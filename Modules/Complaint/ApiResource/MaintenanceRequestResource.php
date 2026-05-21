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
            'title'            => $this->title,
            'description'      => $this->description,

            'category' => $this->category,

            // keep API property name `is_urgent` for backward compatibility
            // but return the new urgency level (low|medium|high|urgent)
            'is_urgent' => $this->urgency ?? 'low',

            'status' => $this->status,

            'rejection_reason' => $this->rejection_reason,
            'attachments'      => $this->attachments ?? [],

            'created_at' => $this->created_at?->format('d F Y, h:i A'),
            'updated_at' => $this->updated_at?->format('d F Y, h:i A'),

            'mosque' => $this->whenLoaded(
                'mosque',
                fn() => [
                    'id'   => $this->mosque->id,
                    'name' => $this->mosque->name,
                ],
            ),

            'region_manager' => $this->whenLoaded(
                'regionManager',
                fn() => [
                    'id'   => $this->regionManager->id,
                    'name' => $this->regionManager->name,
                ],
            ),
        ];
    }
}
