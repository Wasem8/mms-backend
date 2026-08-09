<?php

namespace Modules\User\Repository;

use Modules\User\DTOs\UpdateProfileDTO;
use Modules\User\Models\User;
use Modules\User\Repository\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        $nameParts = explode(' ', $dto->fullName, 2);

        $user->update([
            'first_name' => $nameParts[0],
            'last_name'  => $nameParts[1] ?? '',
            'name'       => $dto->fullName,
            'phone'      => $dto->phone,
            'email'      => $dto->email,
        ]);

        return $user->fresh();
    }
}
