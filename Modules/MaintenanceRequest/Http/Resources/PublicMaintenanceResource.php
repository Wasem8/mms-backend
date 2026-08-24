<?php

namespace Modules\MaintenanceRequest\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicMaintenanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'maintenance_number' => $this->maintenance_number,
            'title'              => $this->title,
            'description'        => $this->description,
            'category'           => $this->category,
            'priority'           => $this->priority,
            'status'             => $this->status,
            'scheduled_at'       => $this->scheduled_at,
            'completed_at'       => $this->completed_at,
            'created_at'         => $this->created_at,
            'mosque'             => $this->whenLoaded('mosque', fn() => [
                'id'   => $this->mosque->id,
                'name' => $this->mosque->name,
            ]),
            'files' => $this->whenLoaded('files', fn() => $this->files->map(fn($f) => [
                'file_path' => $f->file_path,
                'file_name' => $f->file_name,
            ])),
        ];
    }
}
