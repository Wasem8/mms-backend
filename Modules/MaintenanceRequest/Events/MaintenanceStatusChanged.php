<?php

namespace Modules\MaintenanceRequest\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\MaintenanceRequest\Models\Maintenance;

class MaintenanceStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Maintenance $maintenance,
        public string $oldStatus,
        public string $newStatus,
        public ?string $note,
        public string $changedBy,
    ) {}
}
