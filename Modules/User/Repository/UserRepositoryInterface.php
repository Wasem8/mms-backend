<?php

namespace Modules\User\Repository;

use Modules\User\DTOs\UpdateProfileDTO;
use Modules\User\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function updateProfile(User $user, UpdateProfileDTO $dto): User;
}
