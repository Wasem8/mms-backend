<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Models\User;

class ChangeUserStatusAction
{
    public function execute(
        User $actor,
        User $target,
        string $status
    ): User {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'status' => 'حالة الحساب غير صالحة.',
            ]);
        }

        // لا يمكن للمستخدم تغيير حالته بنفسه
        if ($actor->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => 'لا يمكنك تغيير حالة حسابك بنفسك.',
            ]);
        }

        // يجب أن يكون صاحب الطلب أعلى من المستهدف
        if (!$actor->canManageUser($target)) {
            throw ValidationException::withMessages([
                'user' => 'لا تملك صلاحية إدارة حالة هذا المستخدم.',
            ]);
        }

        // التحقق من نطاق المسجد
        if (!$this->canManageWithinScope($actor, $target)) {
            throw ValidationException::withMessages([
                'user' => 'لا يمكنك إدارة مستخدم خارج نطاق صلاحياتك.',
            ]);
        }

        // 🎯 [التعديل الجديد]: منع التفعيل إذا كان هناك مستخدم نشط بنفس الدور الأحادي في المسجد
        if ($status === 'active') {
            $this->ensureNoActiveConflict($target);
        }

        $target->update([
            'status' => $status,
        ]);

        return $target->fresh();
    }

    private function canManageWithinScope(
        User $actor,
        User $target
    ): bool {
        // Super Admin يستطيع إدارة الجميع
        if ($actor->hasRole('super_admin')) {
            return true;
        }

        // بقية الأدوار مرتبطة بالمسجد
        return $actor->mosque_id !== null
            && $actor->mosque_id === $target->mosque_id;
    }

    /**
     * التحقق من عدم وجود مستخدم نشط آخر يمتلك دوراً أُحادياً في نفس المسجد
     */
    private function ensureNoActiveConflict(User $target): void
    {
        // إذا لم يكن المستهدف مرتبكاً بمسجد، لا داعي للفحص
        if (!$target->mosque_id) {
            return;
        }

        // الأدوار التي يُسمح بوجود شخص واحد فقط منها في المسجد
        $uniqueRoles = ['mosque_manager', 'halaqa_supervisor'];

        foreach ($uniqueRoles as $role) {
            if ($target->hasRole($role)) {

                // البحث عن مستخدم آخر نشط ينتمي لنفس المسجد ويمتلك نفس الدور
                $hasActiveConflict = User::where('mosque_id', $target->mosque_id)
                    ->where('id', '!=', $target->id) // استثناء المستهدف نفسه
                    ->where('status', 'active')
                    ->whereHas('roles', function ($q) use ($role) {
                        $q->where('name', $role);
                    })->exists();

                if ($hasActiveConflict) {
                    $roleTitle = $role === 'mosque_manager' ? 'مدير مسجد' : 'مشرف حلقات';

                    throw ValidationException::withMessages([
                        'status' => "لا يمكن تفعيل هذا الحساب. يوجد بالفعل ($roleTitle) نشط حالياً لهذا المسجد."
                    ]);
                }
            }
        }
    }
}
