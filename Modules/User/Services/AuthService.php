<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\Hash;
use Modules\User\app\Services\ValidationException;
use Modules\User\Models\User;

class AuthService
{
    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // إذا تستخدم Laravel Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
