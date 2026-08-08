<?php

namespace Modules\Community\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Community\Models\Sermon;

class SermonApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Sermon $sermon,
        public int $regionManagerId,
    ) {}
}
