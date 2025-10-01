<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers to prevent blocking by firewalls and anti-malware
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        
        // Content Security Policy for enhanced security
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.gstatic.com https://www.google.com https://apis.google.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.jsdelivr.net; " .
               "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https: wss: ws:; " .
               "media-src 'self' blob:; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self' https:; " .
               "frame-ancestors 'self';";
        
        $response->headers->set('Content-Security-Policy', $csp);
        
        // Additional security headers
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        // Force HTTPS for all URLs
        if (app()->environment('production')) {
            $response->headers->set('Content-Security-Policy-Report-Only', "upgrade-insecure-requests");
        }
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Download-Options', 'noopen');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        
        // Hide server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}