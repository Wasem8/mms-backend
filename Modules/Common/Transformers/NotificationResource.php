<?php

namespace Modules\Common\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'extra_data' => $this->data,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at ? $this->read_at->format('Y-m-d H:i') : null,
            'created_since' => Carbon::parse($this->created_at)->diffForHumans(),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
