<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'student' => [
                'id' => $this->student?->id,
                'name' => $this->student?->first_name . ' ' . $this->student?->last_name,
            ],

            'halaqa' => [
                'id' => $this->halaqa?->id,
                'name' => $this->halaqa?->name,
            ],

            'score' => $this->score,
            'notes' => $this->notes,
            'evaluated_at' => $this->evaluated_at,
        ];
    }
}
