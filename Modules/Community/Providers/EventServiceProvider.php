<?php

namespace Modules\Community\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Community\Events\SermonApproved;
use Modules\Community\Events\SermonRejected;
use Modules\Community\Events\SermonSelectedForFriday;
use Modules\Community\Events\TameemSent;
use Modules\Community\Events\TameemUpdated;
use Modules\Community\Listeners\SendSermonApprovedNotification;
use Modules\Community\Listeners\SendSermonRejectedNotification;
use Modules\Community\Listeners\SendSermonSelectedNotification;
use Modules\Community\Listeners\SendTameemSentNotification;
use Modules\Community\Listeners\SendTameemUpdatedNotification;
use Modules\Community\Events\DawahProgramCreated;
use Modules\Community\Listeners\SendDawahProgramCreatedNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SermonApproved::class => [
            SendSermonApprovedNotification::class,
        ],
        SermonRejected::class => [
            SendSermonRejectedNotification::class,
        ],
        SermonSelectedForFriday::class => [
            SendSermonSelectedNotification::class,
        ],
        TameemSent::class => [
            SendTameemSentNotification::class,
        ],
        TameemUpdated::class => [
            SendTameemUpdatedNotification::class,
        ],
        DawahProgramCreated::class => [
            SendDawahProgramCreatedNotification::class,
        ],
    ];

    protected static $shouldDiscoverEvents = true;

    protected function configureEmailVerification(): void {}
}
