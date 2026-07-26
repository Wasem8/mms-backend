<?php

declare(strict_types=1);

namespace Modules\User\Transformers;

use App\Http\Resources\Common\BaseApiResource;
use Illuminate\Http\Request;

class UserResource extends BaseApiResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone' => $this->phone,
            'status'     => $this->status,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'roles'      => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'permissions' => $this->whenLoaded('roles', function () {
                return $this->roles->flatMap(fn($role) => $role->permissions)->pluck('name')->unique();
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
