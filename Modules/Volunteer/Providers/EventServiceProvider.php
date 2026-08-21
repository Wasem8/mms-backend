<?php

namespace Modules\Volunteer\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Volunteer\Events\ApplicationStatusChanged;
use Modules\Volunteer\Events\CertificateIssued;
use Modules\Volunteer\Events\OpportunityCreated;
use Modules\Volunteer\Listeners\NotifyVolunteerOfApplicationStatus;
use Modules\Volunteer\Listeners\NotifyVolunteerOfCertificate;
use Modules\Volunteer\Listeners\NotifyVolunteerOfOpportunityCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OpportunityCreated::class => [
            NotifyVolunteerOfOpportunityCreated::class,
        ],
        ApplicationStatusChanged::class => [
            NotifyVolunteerOfApplicationStatus::class,
        ],
        CertificateIssued::class => [
            NotifyVolunteerOfCertificate::class,
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
