<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\User\Actions\LoginAction;
use Modules\User\Http\Requests\LoginRequest;
use Modules\User\Transformers\UserResource;
use Modules\Volunteer\Actions\RegisterVolunteerAction;
use Modules\Volunteer\Http\Requests\RegisterVolunteerRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class VolunteerAuthController extends Controller
{
    public function register(RegisterVolunteerRequest $request, RegisterVolunteerAction $action)
    {
        $user = $action->execute($request->validated());

        $token = JWTAuth::fromUser($user);

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], __('messages.volunteer_registered'), 201);
    }

    public function login(LoginRequest $request, LoginAction $action)
    {
        $result = $action->execute($request->validated());

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        $user = $result;

        $token = JWTAuth::fromUser($user);

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('roles.permissions')),
        ], __('messages.login_successful'));
    }
}
