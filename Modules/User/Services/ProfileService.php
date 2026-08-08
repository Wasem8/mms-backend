<?php

namespace Modules\User\Services;


namespace Modules\User\Services;

use Modules\User\DTOs\UpdateProfileDTO;
use Modules\User\Models\User;
use Modules\User\Repository\UserRepositoryInterface;

class ProfileService
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function getProfile(User $user): User
    {
        return $user->loadMissing('mosque');
    }

    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        return $this->userRepository->updateProfile($user, $dto)->loadMissing('mosque');
    }
}
