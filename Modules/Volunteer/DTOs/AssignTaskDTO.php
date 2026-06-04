<?php

namespace Modules\Volunteer\DTOs;

readonly class AssignTaskDTO
{
    public function __construct(
        public int    $applicationId,
        public string $taskDescription,
    ) {}
}
