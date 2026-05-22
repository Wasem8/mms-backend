<?php

namespace Modules\Complaint\ApiResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference_number' => $this->reference_number,
            'mosque_id'        => $this->mosque_id,
            'title'            => $this->title,
            'description'      => $this->description,
            'category'         => $this->category,
            'urgency'          => $this->urgency,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'attachments'      => $this->whenLoaded(
                'files',
                fn() =>
                $this->files->map(fn($f) => [
                    'url'       => $f->file,
                    'file_type' => $f->file_type,
                ])
            ),
            'created_at'       => $this->created_at->format('d M Y, h:i A'),
            'updated_at'       => $this->updated_at->format('d M Y, h:i A'),
            'mosque'           => $this->whenLoaded('mosque', fn() => [
                'id'   => $this->mosque->id,
                'name' => $this->mosque->name,
            ]),
        ];
    }
}
