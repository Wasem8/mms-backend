<?php

namespace Modules\Invitation\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'mosque' => [
                'id' => $this->mosque_id,
                'name' => $this->mosque?->name,
            ]
        ];
    }
}
