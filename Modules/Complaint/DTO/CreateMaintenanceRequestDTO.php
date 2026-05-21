<?php

namespace Modules\Complaint\DTO;


readonly class CreateMaintenanceRequestDTO
{
    public function __construct(
        public int     $mosqueId,
        public string  $title,
        public string  $description,
        public string  $category,
        // urgency levels: low, medium, high, urgent
        public string  $isUrgent    = 'low',
        public ?array  $attachments = null,
    ) {}
}
