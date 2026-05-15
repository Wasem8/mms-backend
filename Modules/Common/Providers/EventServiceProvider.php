<?php

namespace Modules\Common\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Common\Listeners\SendAttendanceNotification;
use Modules\Common\Listeners\SendEvaluationNotification;
use Modules\Education\Events\AttendanceRecorded;
use Modules\Education\Events\EvaluationUpdated;
use Modules\Education\Events\StudentEvaluated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        StudentEvaluated::class => [
            SendEvaluationNotification::class,
        ],
        EvaluationUpdated::class => [
            SendEvaluationNotification::class,
        ],

        AttendanceRecorded::class => [
            SendAttendanceNotification::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
