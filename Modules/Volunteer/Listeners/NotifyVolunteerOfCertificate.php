<?php

namespace Modules\Volunteer\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Volunteer\Events\CertificateIssued;

class NotifyVolunteerOfCertificate
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(CertificateIssued $event): void
    {
        $volunteer = $event->certificate->volunteer;

        // Mail::to($volunteer->email)->send(new CertificateMail($event->certificate));
    }
}
