<?php

namespace Modules\Community\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Community\Repositories\SermonRepository;
use Modules\Community\Repositories\SermonRepositoryInterface;
use Modules\Community\Repositories\TameemRepository;
use Modules\Community\Repositories\TameemRepositoryInterface;

class CommunityServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Community';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'community';

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
            SermonRepositoryInterface::class,
            SermonRepository::class,
        );

        $this->app->bind(
            TameemRepositoryInterface::class,
            TameemRepository::class,
        );

        $this->app->bind(
            \Modules\Community\Repositories\DawahProgramRepositoryInterface::class,
            \Modules\Community\Repositories\DawahProgramRepository::class
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
