<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Common\BaseApiResource;
use Illuminate\Http\Request;

class UserResource extends BaseApiResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'is_verified' => $this->email_verified_at !== null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'roles'      => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
        ];
    }
}
