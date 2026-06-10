<?php

namespace Modules\Education\Services\Sync\Contracts;

interface SyncOperationInterface
{
    public function handle(
        string $uuid,
        array $data
    ): array;
}
