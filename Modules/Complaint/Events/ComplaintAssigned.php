<?php

namespace Modules\Complaint\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Complaint\Models\Complaint;
use Modules\User\Models\User;

class ComplaintAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Complaint $complaint,
        public User $assignedAdmin,
        public int $assignedBy,
        public ?string $note = null
    ) {}
}
