<?php

namespace Modules\Volunteer\DTOs;


readonly class CreateTaskDto
{
    public function __construct(
        public int    $opportunityId,
        public string $taskDescription,
    ) {}
}
