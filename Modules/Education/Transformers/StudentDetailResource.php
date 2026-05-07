<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $total_records = ($this->total_present ?? 0) + ($this->total_absent ?? 0);
        $attendance_rate = $total_records > 0
            ? round(($this->total_present / $total_records) * 100, 2) . '%'
            : '0%';

        return [
            'id' => $this->id,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'parent' => [
                'id' => $this->parent?->id,
                'name' => $this->parent?->name,
                'email' => $this->parent?->email,
            ],
            'mosque' => [
                'id' => $this->mosque?->id,
                'name' => $this->mosque?->name,
            ],
            'halaqats' => $this->halaqats->map(fn($h) => [
                'id' => $h->id,
                'name' => $h->name,
            ]),
            'statistics' => [
                'total_absent_days' => $this->total_absent ?? 0,
                'total_present_days' => $this->total_present ?? 0,
                'attendance_rate' => $attendance_rate,
                'last_attendance_at' => $this->last_presence ?? 'لم يتم التسجيل بعد',
            ],
            'profile' => [
                'date_of_birth' => $this->date_of_birth,
                'gender' => $this->gender,
                'status' => $this->status,
                'joined_at' => $this->created_at->format('Y-m-d'),
            ]
        ];
    }
}
