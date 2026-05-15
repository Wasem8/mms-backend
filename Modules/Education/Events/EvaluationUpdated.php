<?php

namespace Modules\Education\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Education\Models\Evaluation;

class EvaluationUpdated
{
    use SerializesModels;
    public $evaluation;

    public function __construct(Evaluation $evaluation) {
        $this->evaluation = $evaluation;
    }
}
