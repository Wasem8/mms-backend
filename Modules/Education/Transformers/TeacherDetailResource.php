<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'halaqats' => $this->halaqats->map(fn($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'stats' => [
                    'total_students' => $h->students_count,
                    'total_present_all_time' => $h->total_present_count,
                    'total_absent_all_time'  => $h->total_absent_count,
                    // نسبة الحضور العام (إجمالي الحضور ÷ إجمالي سجلات التحضير)
                    'overall_attendance_rate' => ($h->total_present_count + $h->total_absent_count) > 0
                        ? round(($h->total_present_count / ($h->total_present_count + $h->total_absent_count)) * 100, 2) . '%'
                        : '0%'
                ]
            ]),
        ];
    }
}
