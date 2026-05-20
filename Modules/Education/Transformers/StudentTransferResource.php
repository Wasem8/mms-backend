<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentTransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'status' => $this->status,
            'halaqats' => $this->halaqats->map(function ($halaqa) {
                return [
                    'id' => $halaqa->id,
                    'name' => $halaqa->name,
                    'teacher_name' => $halaqa->teacher?->name, // اختياري
                    'joined_at' => $halaqa->pivot->joined_at,
                ];
            }),
        ];
    }
}
