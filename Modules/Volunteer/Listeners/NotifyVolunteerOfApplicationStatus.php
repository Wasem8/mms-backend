<?php

namespace Modules\Volunteer\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Volunteer\Events\ApplicationStatusChanged;


class NotifyVolunteerOfApplicationStatus
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(ApplicationStatusChanged  $event): void
    {
        $volunteer = $event->application->volunteer;
        $status    = $event->application->status->value;

        // Mail::to($volunteer->email)->send(new ApplicationStatusMail($event->application));
        // or: Notification::send($volunteer, new ApplicationStatusNotification($event->application));
    }
}
