<?php

namespace Modules\Mosque\Policies;

use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class MosquePolicy
{

    public function manage(User $user, Mosque $mosque): bool
    {
        return $mosque->manager_id === $user->id;
    }
    /**
     * تعديل البيانات الوصفية العامة (name, image, working_hours, imam, khatib)
     */
    public function update(User $user, Mosque $mosque): bool
    {
        return $this->isPrivileged($user)
            || ($user->hasRole('mosque_manager') && $mosque->manager_id === $user->id);
    }

    /**
     * الحقول الإدارية: status, is_featured, city_id, district_id, manager_id
     */
    public function manageAdministrative(User $user, Mosque $mosque): bool
    {
        return $this->isPrivileged($user);
    }

    /**
     * التقييم محسوب من النظام فقط — لا حتى الإدارة العليا تلمسه يدوياً عادةً，
     * لكن أبقيها حكراً على SUPER_ADMIN لحالات التصحيح اليدوي الاستثنائية
     */
    public function updateRating(User $user, Mosque $mosque): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Mosque $mosque): bool
    {
        return $this->isPrivileged($user);
    }

    private function isPrivileged(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
