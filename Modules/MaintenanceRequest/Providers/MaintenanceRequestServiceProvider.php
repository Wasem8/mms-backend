<?php

namespace Modules\MaintenanceRequest\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\MaintenanceRequest\Console\NotifyDelayedMaintenanceCommand;
use Modules\MaintenanceRequest\Repositories\MaintenanceRepository;
use Modules\MaintenanceRequest\Repositories\MaintenanceRepositoryInterface;
use Modules\MaintenanceRequest\Repositories\MaintenanceStatsRepository;
use Modules\MaintenanceRequest\Repositories\MaintenanceStatsRepositoryInterface;

class MaintenanceRequestServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'MaintenanceRequest';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'maintenancerequest';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        NotifyDelayedMaintenanceCommand::class,
    ];

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
        parent::register();
        
        $this->app->bind(
            MaintenanceRepositoryInterface::class,
            MaintenanceRepository::class,
        );

        $this->app->bind(
            MaintenanceStatsRepositoryInterface::class,
            MaintenanceStatsRepository::class,
        );
    }
    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command(NotifyDelayedMaintenanceCommand::class)->dailyAt('08:00');
    }
}
