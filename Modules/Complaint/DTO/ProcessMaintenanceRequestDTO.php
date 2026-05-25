<?php

namespace Modules\Complaint\DTO;


readonly class ProcessMaintenanceRequestDTO
{
    public function __construct(
        public string  $status,
        public int     $regionManagerId,
        public ?string $rejectionReason = null,
    ) {}
}
