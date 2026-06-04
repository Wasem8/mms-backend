<?php

namespace Modules\Volunteer\DTOs;

readonly class LogHoursDTO
{
    public function __construct(
        public int    $volunteerId,
        public int    $opportunityId,
        public float  $loggedHours,
        public string $managerEvaluation,
        public ?string $notes = null,
    ) {}
}
