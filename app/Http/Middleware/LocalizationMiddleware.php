<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->header('Accept-Language', 'ar');

        if (in_array($locale, ['ar', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
