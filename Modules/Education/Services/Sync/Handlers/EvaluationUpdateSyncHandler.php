<?php

namespace Modules\Education\Services\Sync\Handlers;

use Modules\Education\Models\Evaluation;
use Modules\Education\Services\EvaluationService;
use Modules\Education\Services\Sync\Contracts\SyncOperationInterface;
use Modules\Education\Transformers\EvaluationResource;

class EvaluationUpdateSyncHandler
    implements SyncOperationInterface
{
    public function __construct(
        private EvaluationService $evaluationService
    ) {}


    public function handle(
        string $uuid,
        array $data
    ): array {

        $evaluation = Evaluation::find($data['id']);

        if (!$evaluation) {
            return [
                'client_uuid' => $uuid,
                'status' => 'conflict',
                'message' => 'evaluation not found or already deleted',
                'data' => [
                    'code' => 'EVALUATION_NOT_FOUND'
                ]
            ];
        }

        try {
            $updated = $this->evaluationService->update(
                $data['id'],
                $data
            );

            return [
                'client_uuid' => $uuid,
                'status' => 'ok',
                'data' => new EvaluationResource($updated)
            ];

        } catch (\Throwable $e) {

            return [
                'client_uuid' => $uuid,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => [
                    'code' => 'UPDATE_FAILED'
                ]
            ];
        }
    }
}
