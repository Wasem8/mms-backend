<?php

namespace Modules\Volunteer\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Volunteer\Repositories\Contracts\VolunteerOpportunityRepositoryInterface;
use Modules\Volunteer\Repositories\Eloquent\EloquentVolunteerOpportunityRepository;
use Modules\Volunteer\Repositories\Contracts\VolunteerApplicationRepositoryInterface;
use Modules\Volunteer\Repositories\Eloquent\EloquentVolunteerApplicationRepository;
use Modules\Volunteer\Repositories\Contracts\VolunteerTaskRepositoryInterface;
use Modules\Volunteer\Repositories\Eloquent\EloquentVolunteerTaskRepository;
use Modules\Volunteer\Repositories\Contracts\VolunteerEvaluationRepositoryInterface;
use Modules\Volunteer\Repositories\Eloquent\EloquentVolunteerEvaluationRepository;

class VolunteerServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Volunteer';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'volunteer';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];


    public function register(): void
    {
        $this->app->bind(
            VolunteerOpportunityRepositoryInterface::class,
            EloquentVolunteerOpportunityRepository::class,
        );

        $this->app->bind(
            VolunteerApplicationRepositoryInterface::class,
            EloquentVolunteerApplicationRepository::class,
        );

        $this->app->bind(
            VolunteerTaskRepositoryInterface::class,
            EloquentVolunteerTaskRepository::class,
        );

        $this->app->bind(
            VolunteerEvaluationRepositoryInterface::class,
            EloquentVolunteerEvaluationRepository::class,
        );
    }
    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
