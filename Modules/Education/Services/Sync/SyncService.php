<?php

namespace Modules\Education\Services\Sync;



class SyncService
{
    public function __construct(
        private SyncOperationRegistry $registry
    ) {}

    public function sync(array $ops)
    {
        $results = [];

        foreach ($ops as $op) {

            $handler =
                $this->registry->get(
                    $op['type']
                );

            if (!$handler) {
                $results[] = [
                    'status' => 'error'
                ];

                continue;
            }

            $results[] =
                $handler->handle(
                    $op['client_uuid'],
                    $op['data'] ?? []
                );
        }

        return $results;
    }
}
