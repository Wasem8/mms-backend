<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Transformers\UserResource;

class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'parent' => [
                'id' => $this->parent?->id,
                'name' => $this->parent?->name,
            ],
            'mosque' => [
                'id' => $this->mosque?->id,
                'name' => $this->mosque?->name,
            ],
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'status' => $this->status,
        ];
    }
}
