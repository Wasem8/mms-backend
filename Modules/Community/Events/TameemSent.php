<?php

namespace Modules\Community\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Community\Models\Tameem;

class TameemSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Tameem $tameem,
        public array $recipientIds,
    ) {}
}
