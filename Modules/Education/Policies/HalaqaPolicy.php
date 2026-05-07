<?php

namespace Modules\Education\Policies;

use Modules\User\Models\User;
use Modules\Education\Models\Halaqa;

class HalaqaPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('super_admin') || $user->hasRole('mosque_manager');
    }

    public function view(User $user, Halaqa $halaqa)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Halaqa $halaqa)
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Halaqa $halaqa)
    {
        return $user->hasRole('super_admin');
    }
}
