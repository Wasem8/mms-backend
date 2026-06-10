<?php

namespace Modules\Education\Services\Sync\Handlers;

use Modules\Education\Actions\ProcessExcuseAction;
use Modules\Education\Models\AttendanceExcuse;
use Modules\Education\Services\Sync\Contracts\SyncOperationInterface;
use Modules\Education\Transformers\AttendanceExcuseResource;

class ExcuseDecisionSyncHandler implements SyncOperationInterface
{
    public function __construct(
        private ProcessExcuseAction $processExcuseAction
    ) {}

    public function handle(
        string $uuid,
        array $data
    ): array {

        if (
            empty($data['excuse_id']) ||
            empty($data['status'])
        ) {
            return [
                'client_uuid' => $uuid,
                'status' => 'error',
                'data' => [
                    'code' => 'VALIDATION_ERROR'
                ],
                'message' => 'missing excuse decision fields',
            ];
        }

        $excuse = AttendanceExcuse::find(
            $data['excuse_id']
        );

        if (!$excuse) {
            return [
                'client_uuid' => $uuid,
                'status' => 'error',
                'data' => [
                    'code' => 'EXCUSE_NOT_FOUND'
                ],
                'message' => 'excuse not found',
            ];
        }

        if (
            $excuse->halaqa->teacher_id !== auth()->id()
        ) {
            return [
                'client_uuid' => $uuid,
                'status' => 'error',
                'data' => [
                    'code' => 'FORBIDDEN'
                ],
                'message' => 'forbidden',
            ];
        }

        // replay-safe
        if ($excuse->status !== 'pending') {

            return [
                'client_uuid' => $uuid,
                'status' => 'ok',
                'data' => [
                    'code' => 'EXCUSE_ALREADY_PROCESSED',
                    'status' => $excuse->status,
                    'processed_at' => $excuse->processed_at,
                    'final' => true,
                ],
                'message' => 'already processed',
            ];
        }

        $this->processExcuseAction->execute(
            $excuse,
            [
                'status' => $data['status'],
                'admin_comment' =>
                    $data['admin_comment'] ?? null,

                'processed_at' =>
                    $data['processed_at'] ?? now(),
            ]
        );

        $excuse->refresh();

        return [
            'client_uuid' => $uuid,
            'status' => 'ok',
            'data' => new AttendanceExcuseResource(
                $excuse
            ),
            'message' => 'excuse processed',
        ];
    }
}
