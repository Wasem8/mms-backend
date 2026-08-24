<?php

namespace Modules\Complaint\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Complaint\Models\Complaint;

class ComplaintStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Complaint $complaint,
        public string $oldStatus,
        public string $newStatus,
        public ?string $note,
        public int $adminId,
    ) {}
}
