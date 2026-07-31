<?php

namespace Modules\Community\DTOs;

readonly class SelectSermonForFridayDTO
{
    public function __construct(
        public int $sermonId,
        public string $fridayDate,
    ) {}
}
