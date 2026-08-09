<?php

namespace Modules\Mosque\DTOs;


readonly class CreateMosqueTaskDTO
{
    public function __construct(
        public int $mosqueId,
        public int $createdBy,
        public string $title,
        public string $category,
        public string $dueDate,
        public ?string $dueTime = null,
        public bool $isImportant = false,
        public ?string $notes = null,
    ) {}
}
