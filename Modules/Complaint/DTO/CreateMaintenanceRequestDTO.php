<?php

namespace Modules\Complaint\DTO;

readonly class CreateMaintenanceRequestDTO
{
    public function __construct(
        public int $mosqueId,
        public string $title,
        public string $description,
        public string $category,
        public string $urgency = 'low',
        public ?array $attachments = null,
    ) {}
}
