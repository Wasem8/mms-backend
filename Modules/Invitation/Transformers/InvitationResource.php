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
            'id'           => $this->id,
            'email'        => $this->email,
            'role'         => $this->role,


            'status'       => $this->status,
            'status_label' => $this->status_label,

            'expires_at'   => $this->expires_at?->toIso8601String(),
            'accepted_at'  => $this->accepted_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),

            'mosque' => [
                'id'   => $this->mosque_id,
                'name' => $this->mosque?->name,
            ]
        ];
    }
}
