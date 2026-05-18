<?php

namespace Modules\Donation\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Donation\Repositories\CampaignRepository;
use Modules\Donation\Repositories\CampaignRepositoryInterface;
use Modules\Donation\Repositories\DonationRepository;
use Modules\Donation\Repositories\DonationRepositoryInterface;
use Modules\Donation\Repositories\SettingRepository;
use Modules\Donation\Repositories\SettingRepositoryInterface;

class DonationServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Donation';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'donation';

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

        $this->app->register(RouteServiceProvider::class);


        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
        $this->app->bind(DonationRepositoryInterface::class, DonationRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);

    }


    public function boot(): void
    {
        $this->loadViewsFrom(module_path('Donation', 'resources/views'), 'donation');
        $this->commands([
            \Modules\Donation\Console\ExpireEndedCampaigns::class,
        ]);
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
