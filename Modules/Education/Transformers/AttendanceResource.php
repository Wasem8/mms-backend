<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
            ],

            'halaqa' => [
                'id' => $this->halaqa->id,
                'name' => $this->halaqa->name,
            ],

            'date' => $this->date,
            'status' => $this->status,

            'notes' => $this->notes,

            'created_at' => $this->created_at,
        ];
    }
}
