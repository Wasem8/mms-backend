<?php

namespace Modules\Mosque\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Facility\Repositories\Facilites\FacilityRepository as FacilitesFacilityRepository;
use Modules\Facility\Repositories\Facilites\FacilityRepositoryInterface as FacilitesFacilityRepositoryInterface;
use Modules\Mosque\Repositories\FacilityRepository;
use Modules\Mosque\Repositories\FacilityRepositoryInterface;
use Modules\Mosque\Repositories\MosqueRepository;
use Modules\Mosque\Repositories\MosqueRepositoryInterface;

class MosqueServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Mosque';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'mosque';

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
        // 1. استدعاء دالة الأب لتسجيل الـ Providers (مثل RouteServiceProvider)
        parent::register();

        // 2. تسجيل الـ Repositories الخاصة بك
        $this->app->bind(
            MosqueRepositoryInterface::class,
            MosqueRepository::class,
        );

        $this->app->bind(
            FacilityRepositoryInterface::class,
            FacilityRepository::class
        );
    }/**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
