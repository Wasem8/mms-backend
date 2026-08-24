<?php

namespace Modules\MaintenanceRequest\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\MaintenanceRequest\Events\MaintenanceRequestCreated;
use Modules\MaintenanceRequest\Events\MaintenanceStatusChanged;
use Modules\MaintenanceRequest\Listeners\SendMaintenanceRequestCreatedNotification;
use Modules\MaintenanceRequest\Listeners\SendMaintenanceStatusChangedNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MaintenanceRequestCreated::class => [
            SendMaintenanceRequestCreatedNotification::class,
        ],
        MaintenanceStatusChanged::class => [
            SendMaintenanceStatusChangedNotification::class,
        ],
    ];

    protected static $shouldDiscoverEvents = true;

    protected function configureEmailVerification(): void {}
}
