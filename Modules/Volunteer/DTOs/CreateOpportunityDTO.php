<?php

namespace Modules\Volunteer\DTOs;

readonly class CreateOpportunityDTO
{
    public function __construct(
        public int    $mosqueId,
        public string $title,
        public string $description,
        public int    $requiredVolunteers,
        public string $startDate,
        public string $endDate,
        public array  $tasks = [],
    ) {}
}
