<?php

namespace Modules\Education\Services\Sync\Handlers;

use Modules\Education\Services\EvaluationService;
use Modules\Education\Services\Sync\Contracts\SyncOperationInterface;
use Modules\Education\Transformers\EvaluationResource;

class EvaluationCreateSyncHandler
    implements SyncOperationInterface
{
    public function __construct(
        private EvaluationService $evaluationService
    ) {}

    public function handle(
        string $uuid,
        array $data
    ): array {

        $result = $this->evaluationService->store([
            ...$data,
            'client_uuid' => $uuid,
        ]);

        return [
            'client_uuid' => $uuid,
            'status' => 'ok',
            'data' => new EvaluationResource(
                $result['evaluation']
            )
        ];
    }
}
