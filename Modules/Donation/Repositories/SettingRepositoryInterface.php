<?php

namespace Modules\Donation\Repositories;

interface SettingRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;
}
