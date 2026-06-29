<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherDetailResource extends JsonResource
{
    /**
     * تحويل كائن تفاصيل المعلم العميقة والإحصائيات إلى مصفوفة.
     */
    public function toArray($request): array
    {
        $profile = $this->whenLoaded('teacherProfile');

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $profile ? $profile->phone : null,
            'specialization' => $profile ? $profile->specialization : null,
            'status'         => $profile ? $profile->status : 'active',
            'notes'          => $profile ? $profile->notes : null,


            'halaqats' => $this->whenLoaded('halaqats', function() {
                return $this->halaqats->map(fn($h) => [
                    'id'   => $h->id,
                    'name' => $h->name,
                    'stats' => [
                        'total_students'          => $h->students_count ?? 0,
                        'total_present_all_time'  => $h->total_present_count ?? 0,
                        'total_absent_all_time'   => $h->total_absent_count ?? 0,

                        'overall_attendance_rate' => (($h->total_present_count ?? 0) + ($h->total_absent_count ?? 0)) > 0
                            ? round(($h->total_present_count / ($h->total_present_count + $h->total_absent_count)) * 100, 2) . '%'
                            : '0%'
                    ]
                ]);
            }),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
