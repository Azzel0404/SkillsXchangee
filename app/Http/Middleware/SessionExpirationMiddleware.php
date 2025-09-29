<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SessionExpirationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (Auth::check()) {
            // Check if session has expired
            if (Session::has('last_activity')) {
                $lastActivity = Session::get('last_activity');
                $sessionLifetime = config('session.lifetime', 120) * 60; // Convert to seconds
                
                if (time() - $lastActivity > $sessionLifetime) {
                    // Session has expired
                    Log::info('Session expired for user: ' . Auth::id());
                    
                    // Logout the user
                    Auth::logout();
                    Session::flush();
                    
                    // Redirect to login with session expired message
                    return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
                }
            }
            
            // Update last activity timestamp
            Session::put('last_activity', time());
        }
        
        return $next($request);
    }
}
