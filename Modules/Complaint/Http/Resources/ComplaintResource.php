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
            'assigned_admin' => $this->whenLoaded('assignedAdmin', fn() => $this->assignedAdmin ? [
                'id' => $this->assignedAdmin->id,
                'name' => $this->assignedAdmin->name,
            ] : null),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
