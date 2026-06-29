<?php

namespace Modules\Education\Services\Sync\Handlers;

use Modules\Education\Models\Evaluation;
use Modules\Education\Services\EvaluationService;
use Modules\Education\Services\Sync\Contracts\SyncOperationInterface;

class EvaluationDeleteSyncHandler
    implements SyncOperationInterface
{
    public function __construct(
        private EvaluationService $evaluationService
    ) {}

    public function handle(
        string $uuid,
        array $data
    ): array {



        $evaluation = Evaluation::find(
            $data['id']
        );

        if (!$evaluation) {
            return [
                'client_uuid' => $uuid,
                'status' => 'conflict',
                'message' => 'already deleted'
            ];
        }

        $this->evaluationService->delete(
            $data['id']
        );

        return [
            'client_uuid' => $uuid,
            'status' => 'ok',
            'message' => 'deleted'
        ];
    }
}
