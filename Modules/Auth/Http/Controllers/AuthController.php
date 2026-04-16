<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Http\Requests\LoginWithPasswordRequest;
use Modules\Auth\Http\Requests\SendOtpRequest;
use Modules\Auth\Http\Requests\VerifyOtpRequest;
use Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    // ─────────────────────────────────────────
    //  POST /api/auth/login/password
    // ─────────────────────────────────────────
    public function loginWithPassword(LoginWithPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithPassword(
            email:    $request->email,
            password: $request->password,
            ip:       $request->ip(),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data'    => $result,
        ]);
    }

    // ─────────────────────────────────────────
    //  POST /api/auth/otp/send
    // ─────────────────────────────────────────
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $this->authService->sendOtp($request->email);

        // دائمًا نُرجع نفس الرد (حتى لو البريد غير موجود) — أمان
        return response()->json([
            'success' => true,
            'message' => 'إذا كان البريد مسجلًا، ستصلك رسالة بالرمز.',
        ]);
    }

    // ─────────────────────────────────────────
    //  POST /api/auth/otp/verify
    // ─────────────────────────────────────────
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithOtp(
            email: $request->email,
            otp:   $request->otp,
            ip:    $request->ip(),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data'    => $result,
        ]);
    }

    // ─────────────────────────────────────────
    //  POST /api/auth/logout  (مطلوب Sanctum auth)
    // ─────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج.',
        ]);
    }

    // ─────────────────────────────────────────
    //  POST /api/auth/logout-all
    // ─────────────────────────────────────────
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج من جميع الأجهزة.',
        ]);
    }

    // ─────────────────────────────────────────
    //  GET /api/auth/me
    // ─────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $user->role,
                'redirect_to'  => $this->getRedirectForRole($user->role),
            ],
        ]);
    }

    private function getRedirectForRole(string $role): string
    {
        return match ($role) {
            'region_manager' => '/dashboard/region',
            'mosque_manager' => '/dashboard/mosque',
            'supervisor'     => '/dashboard/supervisor',
            'teacher'        => '/dashboard/teacher',
            'parent'         => '/dashboard/parent',
            default          => '/dashboard',
        };
    }
}
