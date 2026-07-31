<?php

namespace Modules\Community\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SermonSelectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'friday_date' => $this->friday_date->toDateString(),

            // من سيلقي الخطبة يوم الجمعة (صاحب الاختيار)
            'delivering_mosque_manager' => [
                'id' => $this->mosqueManager?->id,
                'name' => $this->mosqueManager?->name,
            ],

            'sermon' => [
                'id' => $this->sermon?->id,
                'title' => $this->sermon?->title,
                'speaker_name' => $this->sermon?->speaker_name,
                'status' => $this->sermon?->status,

                // من كتب الخطبة أصلاً (قد يكون مسجدًا مختلفًا)
                'original_mosque_manager' => [
                    'id' => $this->sermon?->mosqueManager?->id,
                    'name' => $this->sermon?->mosqueManager?->name,
                ],
            ],

            'created_at' => $this->created_at,
        ];
    }
}
