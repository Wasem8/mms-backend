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
                return $this->halaqats->map(function ($h) {
                    $totalPresent = $h->total_present_count ?? 0;
                    $totalAbsent = $h->total_absent_count ?? 0;
                    $total = $totalPresent + $totalAbsent;

                    return [
                        'id'   => $h->id,
                        'name' => $h->name,
                        'stats' => [
                            'total_students'          => $h->students_count ?? 0,
                            'total_present_all_time'  => $totalPresent,
                            'total_absent_all_time'   => $totalAbsent,

                            // 🎯 التعديل المطلوبة من §5: إرجاع رقم مجرد (Float) بدون علامة %
                            'overall_attendance_rate' => $total > 0
                                ? round(($totalPresent / $total) * 100, 2)
                                : 0
                        ]
                    ];
                });
            }),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
