<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Actions\ForgotPasswordAction;
use Modules\User\Actions\LoginAction;
use Modules\User\Actions\RegisterParentAction;
use Modules\User\Actions\ResetPasswordAction;
use Modules\User\Actions\VerifyOtpAction;
use Modules\User\Http\Requests\ForgotPasswordRequest;
use Modules\User\Http\Requests\LoginRequest;
use Modules\User\Http\Requests\RegisterParentRequest;
use Modules\User\Http\Requests\ResetPasswordRequest;
use Modules\User\Http\Requests\VerifyOtpRequest;
use Modules\User\Transformers\UserResource;

class AuthController extends Controller {

    public function registerParent(RegisterParentRequest $request,RegisterParentAction $action)
    {
        $user = $action->execute($request->validated());
        return ApiResponse::success(new UserResource($user), 'Account created. An OTP has been sent to your email for verification.');
    }


    public function verifyOtp(VerifyOtpRequest $request, VerifyOtpAction $action)
    {
        $user = $action->execute($request->validated());

        return ApiResponse::success(
            new UserResource($user),
            'Account verified successfully.'
        );
    }


    public function login(LoginRequest $request, LoginAction $action)
    {
        $result = $action->execute($request->validated());

        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('roles.permissions')),
        ], 'Login successful.');
    }

    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $action)
    {
        $action->execute($request->email);

        return ApiResponse::success([], 'If the email exists, an OTP has been sent.');
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action)
    {
        $action->execute($request->validated());

        return ApiResponse::success([], 'Password reset successfully.');
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::success([],'Logged out successfully.');
    }

}
