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
        $halaqa = $this->halaqats;

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'status' => $this->status,
            'halaqa' => $halaqa ? [
                'id' => $halaqa->id,
                'name' => $halaqa->name,
                'teacher_name' => $halaqa->teacher?->name,
            ] : null,
            'halaqats' => $halaqa ? [[
                'id' => $halaqa->id,
                'name' => $halaqa->name,
                'teacher_name' => $halaqa->teacher?->name,
            ]] : [],
        ];
    }
}
