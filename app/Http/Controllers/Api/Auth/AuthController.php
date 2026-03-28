<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SubmitJoinRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SubmitRegistrationRequest;
use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RegisterStudentAction;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller {

    public function register(SubmitRegistrationRequest $request, RegisterStudentAction $action) {
        $user = $action->execute($request->validated());
        return ApiResponse::success($user,'Registration successful. Please wait for admin approval.');

    }


    public function login(LoginRequest $request, LoginAction $action) {
        $action->execute($request->validated());
        return ApiResponse::success([],'OTP has been sent to your email.');

    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ApiResponse::error('User not found.', 404);
        }


        if ($user->verifyOtp($request->otp)) {
            $token = $user->createToken('auth_token')->plainTextToken;

            $data = [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => new UserResource($user->load('roles')) // هنا التغيير
            ];

            return ApiResponse::success($data, 'Login successful.');
        }

        return ApiResponse::error('Invalid or expired OTP.', 422);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::success([],'Logged out successfully.');
    }

    public function submit(SubmitRegistrationRequest $request, SubmitJoinRequestAction $action)
    {
        $joinRequest = $action->execute($request->validated());

        return ApiResponse::success(
            ['request_id' => $joinRequest->id],
            'Your registration request has been submitted successfully. Please wait for administrative review and account activation.'
        );
    }
}
