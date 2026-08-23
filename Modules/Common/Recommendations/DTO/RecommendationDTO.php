<?php

namespace Modules\Common\Recommendations\DTO;

use Modules\Common\Recommendations\Enums\Severity;

readonly class RecommendationDTO
{
    public function __construct(
        public string $category,
        public Severity $severity,
        public string $text,
    ) {}

    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'severity' => $this->severity->value,
            'text'     => $this->text,
        ];
    }
}
