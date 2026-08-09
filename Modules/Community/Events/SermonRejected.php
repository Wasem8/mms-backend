<?php

namespace Modules\Community\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SermonRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Primitive fields, not a Sermon model reference — hard-reject deletes
    // the row before this event fires, so there's nothing left to hydrate.
    public function __construct(
        public int $sermonId,
        public string $sermonTitle,
        public int $mosqueManagerId,
        public ?string $notes = null,
        public bool $isHardReject = false,
    ) {}
}
