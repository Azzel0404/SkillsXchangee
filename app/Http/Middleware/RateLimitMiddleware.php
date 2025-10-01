<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->ip();
        $maxAttempts = config('security.rate_limit.max_attempts', 100);
        $decayMinutes = config('security.rate_limit.decay_minutes', 1);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response('Too Many Requests.', Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
