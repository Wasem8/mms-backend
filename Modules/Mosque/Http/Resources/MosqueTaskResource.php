<?php

namespace Modules\Mosque\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MosqueTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'category'     => $this->category->value,
            'category_label' => $this->category->label(),
            'due_date'     => $this->due_date->toDateString(),
            'due_time'     => $this->due_time,
            'is_completed' => $this->is_completed,
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'is_important' => $this->is_important,
            'notes'        => $this->notes,
        ];
    }
}
