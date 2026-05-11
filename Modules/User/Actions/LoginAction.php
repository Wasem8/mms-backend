<?php

namespace Modules\User\Actions;

use App\Support\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\User;

class LoginAction
{
    public function execute(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('These credentials are incorrect.', 401);
        }

        if (! $user->email_verified_at) {
            return ApiResponse::error('EMAIL_NOT_VERIFIED', 403);
        }

        if ($user->status !== 'active') {
            return ApiResponse::error('ACCOUNT_INACTIVE', 403);
        }

        return $user;
    }
}
