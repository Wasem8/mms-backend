<?php

namespace Modules\Education\Services;

use Throwable;
use Modules\Education\Models\AttendanceExcuse;
use Modules\Education\Actions\ProcessExcuseAction;
use Modules\Education\Transformers\AttendanceResource;
use Modules\Education\Transformers\EvaluationResource;
use Modules\Education\Transformers\AttendanceExcuseResource;

class SyncService
{
    public function __construct(
        private AttendanceService $attendanceService,
        private EvaluationService $evaluationService,
        private ProcessExcuseAction $processExcuseAction
    ) {}

    public function sync(array $ops): array
    {
        $results = [];

        foreach ($ops as $op) {

            $type = $op['type'] ?? null;
            $uuid = $op['client_uuid'] ?? null;
            $data = $op['data'] ?? [];

            try {

                if (!$type || !$uuid) {
                    $results[] = $this->error(
                        $uuid,
                        'missing type or uuid'
                    );
                    continue;
                }

                switch ($type) {

                    case 'attendance':

                        $results[] = $this->handleAttendanceSync(
                            $uuid,
                            $data
                        );

                        break;

                    case 'evaluation':

                        $results[] = $this->handleEvaluationSync(
                            $uuid,
                            $data
                        );

                        break;

                    case 'excuse_decision':

                        $results[] = $this->handleExcuseDecisionSync(
                            $uuid,
                            $data
                        );

                        break;

                    default:

                        $results[] = $this->error(
                            $uuid,
                            'unknown type'
                        );
                }

            } catch (Throwable $e) {

                $results[] = $this->error(
                    $uuid,
                    $e->getMessage()
                );
            }
        }

        return $results;
    }

    /**
     * Attendance
     */
    private function handleAttendanceSync(
        string $uuid,
        array $data
    ): array {

        if (
            empty($data['halaqa_id']) ||
            empty($data['date'])
        ) {
            return $this->error(
                $uuid,
                'missing attendance required fields'
            );
        }

        $result = $this->attendanceService->storeBulk($data);

        return [
            'client_uuid' => $uuid,
            'status' => 'ok',
            'data' => $result,
            'message' => 'attendance saved'
        ];
    }

    /**
     * Evaluation
     */
    private function handleEvaluationSync(
        string $uuid,
        array $data
    ): array {

        if (
            empty($data['halaqa_id']) ||
            empty($data['student_id']) ||
            !isset($data['score'])
        ) {
            return $this->error(
                $uuid,
                'missing evaluation required fields'
            );
        }

        $result = $this->evaluationService->store([
            ...$data,

            'client_uuid' => $uuid,

            'evaluated_at' => $data['evaluated_at'] ?? now(),

            'synced_at' => now(),
        ]);

        return [
            'client_uuid' => $uuid,
            'status' => $result['is_duplicate']
                ? 'conflict'
                : 'ok',

            'data' => isset($result['evaluation'])
                ? new EvaluationResource(
                    $result['evaluation']
                )
                : null,

            'message' => $result['is_duplicate']
                ? 'already exists'
                : 'evaluation saved',
        ];
    }

    /**
     * Excuse Decision
     */
    private function handleExcuseDecisionSync(
        string $uuid,
        array $data
    ): array {

        if (
            empty($data['excuse_id']) ||
            empty($data['status'])
        ) {
            return $this->error(
                $uuid,
                'missing excuse decision fields'
            );
        }

        $excuse = AttendanceExcuse::find(
            $data['excuse_id']
        );

        if (!$excuse) {

            return $this->error(
                $uuid,
                'excuse not found'
            );
        }

        if ($excuse->halaqa->teacher_id !== auth()->id()) {
            return $this->error($uuid, 'forbidden');
        }

        /**
         * Replay-safe
         */
        if ($excuse->status !== 'pending') {

            return [
                'client_uuid' => $uuid,
                'status' => 'ok',
                'data' => [
                    'code' => 'EXCUSE_ALREADY_PROCESSED',
                    'status' => $excuse->status,
                    'processed_at' => $excuse->updated_at,
                    'final' => true,
                ],
                'message' => 'already processed',
            ];
        }

        $this->processExcuseAction->execute(
            $excuse,
            [
                'status' => $data['status'],
                'admin_comment' => $data['admin_comment'] ?? null,
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

    /**
     * Error
     */
    private function error(
        $uuid,
        string $message
    ): array {

        return [
            'client_uuid' => $uuid,
            'status' => 'error',
            'data' => null,
            'message' => $message,
        ];
    }
}
