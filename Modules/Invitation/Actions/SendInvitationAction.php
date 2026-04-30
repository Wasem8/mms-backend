<?php

namespace Modules\Invitation\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Modules\Invitation\Models\Invitation;
use Modules\Invitation\Notifications\InvitationNotification;
use Modules\User\Models\User;

class SendInvitationAction
{
    public function execute(User $user, string $email, string $role): Invitation
    {

        $permissionName = 'invite_' . $role;

        if (! $user->hasPermission($permissionName)) {
            throw ValidationException::withMessages([
                'role' => 'You are not allowed to invite this role.'
            ]);
        }

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'User already exists and has a role.'
            ]);
        }

        $existingInvitation = Invitation::where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingInvitation) {
            throw ValidationException::withMessages([
                'email' => 'An active invitation already exists for this email.'
            ]);
        }


        $invitation = Invitation::create([
            'email' => $email,
            'role' => $role,
            'created_by' => $user->id,
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $email)
            ->notify(new InvitationNotification($invitation));

        return $invitation;
    }
}
