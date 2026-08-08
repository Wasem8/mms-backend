<?php

namespace Modules\User\Transformers;


use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        [$firstName, $lastName] = $this->resolveNameParts();

        return [
            'id'         => $this->id,
            'first_name' => $this->first_name ?? $firstName,
            'last_name'  => $this->last_name ?? $lastName,
            'full_name'  => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'status'     => $this->status,
            'role'       => $this->roles()->pluck('name')->first(),
            'mosque'     => $this->whenLoaded('mosque', fn() => [
                'id'   => $this->mosque->id,
                'name' => $this->mosque->name,
            ]),
        ];
    }

    private function resolveNameParts(): array
    {
        if (! $this->name) {
            return ['', ''];
        }

        $parts = explode(' ', trim($this->name), 2);

        return [$parts[0], $parts[1] ?? ''];
    }
}
