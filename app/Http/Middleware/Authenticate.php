<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\Support\ApiResponse;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        return null;
    }

    protected function unauthenticated($request, array $guards)
    {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated.',
            'data' => null,
            'pagination' => null
        ], 401);
    }
}
