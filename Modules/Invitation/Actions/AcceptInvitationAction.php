<?php

namespace Modules\Invitation\Actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Invitation\Models\Invitation;
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
            ]);

            $isNewUser = true;
        }

        $role = Role::where('name', $invitation->role)->firstOrFail();

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
}
