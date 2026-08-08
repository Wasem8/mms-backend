<?php

namespace Modules\MaintenanceRequest\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\MaintenanceRequest\Repositories\MaintenanceRepository;
use Modules\MaintenanceRequest\Repositories\MaintenanceRepositoryInterface;

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
        parent::register();
        
        $this->app->bind(
            MaintenanceRepositoryInterface::class,
            MaintenanceRepository::class,
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
