<?php

namespace Modules\Complaint\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'complaint_number' => $this->complaint_number,
            'title' => $this->title,
            'status' => $this->status,
            'priority' => $this->priority,
            'complaint_type' => $this->complaint_type,
            'mosque' => $this->whenLoaded('mosque', fn() => [
                'id' => $this->mosque->id,
                'name' => $this->mosque->name,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
