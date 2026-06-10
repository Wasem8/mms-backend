<?php

namespace Modules\Education\Services\Sync\Handlers;

use Modules\Education\Services\AttendanceService;
use Modules\Education\Services\Sync\Contracts\SyncOperationInterface;

class AttendanceSyncHandler implements SyncOperationInterface
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    public function handle(
        string $uuid,
        array $data
    ): array {

        if (
            empty($data['halaqa_id']) ||
            empty($data['date'])
        ) {
            return [
                'client_uuid' => $uuid,
                'status' => 'error',
                'data' => [
                    'code' => 'VALIDATION_ERROR'
                ],
                'message' => 'missing attendance required fields',
            ];
        }

        $result = $this->attendanceService->storeBulk(
            $data
        );


        return [
            'client_uuid' => $uuid,
            'status' => 'ok',
            'data' => $result,
            'message' => 'attendance saved',
        ];
    }
}
