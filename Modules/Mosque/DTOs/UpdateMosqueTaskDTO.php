<?php

namespace Modules\Mosque\DTOs;

readonly class UpdateMosqueTaskDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $category = null,
        public ?string $dueDate = null,
        public ?string $dueTime = null,
        public ?bool $isImportant = null,
        public ?string $notes = null,
    ) {}
}

