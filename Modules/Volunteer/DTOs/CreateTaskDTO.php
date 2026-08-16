<?php

namespace Modules\Volunteer\DTOs;


readonly class CreateTaskDTO
{
    public function __construct(
        public int    $opportunityId,
        public string $taskDescription,
    ) {}
}
