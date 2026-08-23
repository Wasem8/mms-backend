<?php

namespace Modules\Complaint\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Complaint\Console\NotifyStuckComplaintsCommand;
use Modules\Complaint\Repositories\ComplaintRepository;
use Modules\Complaint\Repositories\ComplaintRepositoryInterface;
use Modules\Complaint\Repositories\ComplaintStatsRepository;
use Modules\Complaint\Repositories\ComplaintStatsRepositoryInterface;
use Modules\Complaint\Repositories\MaintenanceRequestRepository;
use Modules\Complaint\Repositories\MaintenanceRequestRepositoryInterface;

class ComplaintServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Complaint';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'complaint';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        NotifyStuckComplaintsCommand::class,
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
        
        $this->app->bind(ComplaintRepositoryInterface::class, ComplaintRepository::class);
        $this->app->bind(ComplaintStatsRepositoryInterface::class, ComplaintStatsRepository::class);
    }
    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command(NotifyStuckComplaintsCommand::class)->dailyAt('08:05');
    }
}
