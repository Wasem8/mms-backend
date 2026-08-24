<?php

namespace Modules\MaintenanceRequest\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\MaintenanceRequest\Models\Maintenance;

class MaintenanceRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Maintenance $maintenance,
    ) {}
}
