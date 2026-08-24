<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * تحويل كائن المعلم والبروفايل لعرضه في الـ API.
     */
    public function toArray(Request $request): array
    {
        $profile = $this->whenLoaded('teacherProfile');

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'mosque_id'      => $this->mosque_id,

            'phone'          => $profile ? $profile->phone : null,
            'specialization' => $profile ? $profile->specialization : null,
            'status'         => $profile ? $profile->status : 'active',
            'notes'          => $profile ? $profile->notes : null,

            // 🎯 إرجاع عدد الحلقات والطلاب التابعين للمعلم
            'halaqats_count' => $this->halaqats_count ?? 0,
            'students_count' => $this->students_count ?? 0,

            'halaqats'       => $this->whenLoaded('halaqats', function () {
                return $this->halaqats->map(function ($halaqa) {
                    return [
                        'id'   => $halaqa->id,
                        'name' => $halaqa->name,
                    ];
                });
            }),

            'created_at'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
