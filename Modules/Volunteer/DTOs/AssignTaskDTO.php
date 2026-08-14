<?php

namespace Modules\Volunteer\DTOs;

readonly class AssignTaskDTO
{
    public function __construct(
        public int $taskId,
        public int $applicationId,
    ) {}
}
