<?php

namespace Modules\Invitation\Actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Invitation\Models\Invitation;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\Role;
use Modules\User\Models\User;

class AcceptInvitationAction
{
    public function execute(array $data): array
    {
        $invitation = Invitation::where('token', $data['token'])->first();

        if (! $invitation || ! $invitation->isValid()) {
            throw ValidationException::withMessages([
                'token' => 'Invalid or expired invitation'
            ]);
        }

        $user = User::where('email', $invitation->email)->first();

        $isNewUser = false;

        if (! $user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'mosque_id' => $invitation->mosque_id,
                'email_verified_at' => now(),
            ]);

            $isNewUser = true;
        }

        $role = Role::where('name', $invitation->role)->firstOrFail();

        if ($invitation->role === 'mosque_manager') {
            $this->assignAsMosqueManager($user, $invitation->mosque_id);
        }

        $user->roles()->syncWithoutDetaching([$role->id]);

        $invitation->update([
            'accepted_at' => now()
        ]);

        return [
            'user' => $user,
            'is_new_user' => $isNewUser,
            'role' => $role->name,
        ];
    }

    private function assignAsMosqueManager(User $user, int $mosqueId): void
    {
        $alreadyManages = Mosque::where('manager_id', $user->id)
            ->where('id', '!=', $mosqueId)
            ->exists();

        if ($alreadyManages) {
            throw ValidationException::withMessages([
                'role' => 'هذا المستخدم مدير مسجد آخر بالفعل، لا يمكن ربطه بمسجد إضافي.'
            ]);
        }

        $mosqueHasOtherManager = Mosque::where('id', $mosqueId)
            ->whereNotNull('manager_id')
            ->where('manager_id', '!=', $user->id)
            ->exists();

        if ($mosqueHasOtherManager) {
            throw ValidationException::withMessages([
                'role' => 'هذا المسجد يمتلك مدير مسجد نشط بالفعل.'
            ]);
        }

        Mosque::where('id', $mosqueId)->update(['manager_id' => $user->id]);
    }
}
