<?php

namespace Modules\Education\Services\Sync;

use Modules\Education\Services\Sync\Handlers\AttendanceSyncHandler;
use Modules\Education\Services\Sync\Handlers\EvaluationCreateSyncHandler;
use Modules\Education\Services\Sync\Handlers\EvaluationDeleteSyncHandler;
use Modules\Education\Services\Sync\Handlers\EvaluationUpdateSyncHandler;
use Modules\Education\Services\Sync\Handlers\ExcuseDecisionSyncHandler;

class SyncOperationRegistry
{
    public function __construct(
        private AttendanceSyncHandler $attendance,
        private EvaluationCreateSyncHandler $evaluation,
        private EvaluationUpdateSyncHandler $evaluationUpdate,
        private EvaluationDeleteSyncHandler $evaluationDelete,
        private ExcuseDecisionSyncHandler $excuseDecision
    ) {}

    public function get(string $type)
    {
        return match ($type) {

            'attendance'
            => $this->attendance,

            'evaluation'
            => $this->evaluation,

            'evaluation_update'
            => $this->evaluationUpdate,

            'evaluation_delete'
            => $this->evaluationDelete,

            'excuse_decision'
            => $this->excuseDecision,

            default => null
        };
    }
}
