<?php

namespace Modules\User\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Modules\User\Repository\UserRepositoryInterface;
use Modules\User\Repository\EloquentUserRepository;

class UserServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'User';
    protected string $nameLower = 'user';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
    }
}
