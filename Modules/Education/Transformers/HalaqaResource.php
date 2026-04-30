<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class HalaqaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'schedule_days' => $this->schedule_days,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'capacity' => $this->capacity,

            'teacher' => [
                'id' => $this->teacher?->id,
                'name' => $this->teacher?->name,
            ],

            'mosque' => [
                'id' => $this->mosque?->id,
                'name' => $this->mosque?->name,
            ],

            'students_count' => $this->students()->count(),
            'students' => StudentResource::collection($this->whenLoaded('students')),
        ];
    }
}
