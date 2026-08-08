<?php

namespace Modules\User\DTOs;

readonly class UpdateProfileDTO
{
    public function __construct(
        public string $fullName,
        public string $phone,
        public string $email,
    ) {}
}
