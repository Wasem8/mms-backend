<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceExcuseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client_uuid' => $this->client_uuid,
            'student_id' => $this->student_id,
            'halaqa_id' => $this->halaqa_id,
            'absence_date' => $this->absence_date,
            'reason' => $this->reason,
            'status' => $this->status,
            'admin_comment' => $this->admin_comment,

            'student' => [
                'id' => $this->student?->id,
                'name' => $this->student?->first_name . ' ' . $this->student?->last_name,
            ],

            'halaqa' => [
                'id' => $this->halaqa?->id,
                'name' => $this->halaqa?->name,
            ],
        ];
    }
}
