<?php

namespace Modules\Complaint\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Complaint\Events\ComplaintSubmitted;
use Modules\Complaint\Events\ComplaintStatusChanged;
use Modules\Complaint\Listeners\SendComplaintSubmittedNotification;
use Modules\Complaint\Listeners\SendComplaintStatusChangedNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ComplaintSubmitted::class => [
            SendComplaintSubmittedNotification::class,
        ],
        ComplaintStatusChanged::class => [
            SendComplaintStatusChangedNotification::class,
        ],
    ];

    protected static $shouldDiscoverEvents = true;

    protected function configureEmailVerification(): void {}
}
