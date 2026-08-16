<?php

namespace Modules\Volunteer\DTOs;

readonly class UpdateOpportunityDTO
{
    public function __construct(
        public ?string $title               = null,
        public ?string $description         = null,
        public ?int    $requiredVolunteers   = null,
        public ?string $startDate           = null,
        public ?string $endDate             = null,
        public ?array  $tasks                = null,
    ) {}
}
