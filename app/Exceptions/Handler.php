<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\View\ViewException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle ViewException (like undefined variable errors)
        if ($exception instanceof ViewException) {
            $message = $exception->getMessage();
            
            // Check if it's a session-related error
            if (strpos($message, 'Undefined variable') !== false && 
                (strpos($message, 'expiredSessions') !== false || 
                 strpos($message, 'userStats') !== false ||
                 strpos($message, 'stats') !== false)) {
                
                Log::warning('ViewException: ' . $message . ' for user: ' . (Auth::id() ?? 'guest'));
                
                // If user is authenticated, redirect to dashboard with proper data
                if (Auth::check()) {
                    return redirect()->route('dashboard')->with('error', 'There was an issue loading your dashboard. Please try again.');
                } else {
                    // If not authenticated, redirect to login
                    return redirect()->route('login')->with('error', 'Please log in to access your dashboard.');
                }
            }
        }

        return parent::render($request, $exception);
    }
}
