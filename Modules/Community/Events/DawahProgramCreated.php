<?php

namespace Modules\Community\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Community\Models\DawahProgram;

class DawahProgramCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DawahProgram $program,
        public int $creatorId,
    ) {}
}
