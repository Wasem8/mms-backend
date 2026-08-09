<?php

namespace Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بالوصول.',
                'data' => null,
                'pagination' => null,
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'حسابك غير مفعل حالياً.',
                'data' => null,
                'pagination' => null,
            ], 403);
        }

        return $next($request);
    }
}
