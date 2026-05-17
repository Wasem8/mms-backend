<?php

namespace Modules\Donation\Repositories;

use Modules\Donation\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    #[\Override]
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    #[\Override]
    public function set(string $key, mixed $value): void
    {
        Setting::set($key, $value);
    }
}
