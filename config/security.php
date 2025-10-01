<?php

return [
    'rate_limit' => [
        'max_attempts' => env('RATE_LIMIT_MAX_ATTEMPTS', 100),
        'decay_minutes' => env('RATE_LIMIT_DECAY_MINUTES', 1),
    ],

    'content_security_policy' => [
        'default-src' => ["'self'"],
        'script-src' => [
            "'self'",
            "'unsafe-inline'", // Consider removing in production if possible
            "'unsafe-eval'",   // Consider removing in production if possible
            "https://cdn.jsdelivr.net",
            "https://www.gstatic.com",
            "https://*.firebaseio.com",
            "https://*.firebaseapp.com",
            "https://*.googleapis.com",
            "https://*.pusher.com",
        ],
        'style-src' => [
            "'self'",
            "'unsafe-inline'",
            "https://cdn.jsdelivr.net",
            "https://fonts.googleapis.com",
            "https://fonts.bunny.net",
        ],
        'img-src' => [
            "'self'",
            "data:",
            "https://www.gravatar.com",
        ],
        'font-src' => [
            "'self'",
            "https://fonts.gstatic.com",
            "https://fonts.bunny.net",
            "https://cdn.jsdelivr.net",
        ],
        'connect-src' => [
            "'self'",
            "https://*.firebaseio.com",
            "wss://*.pusher.com",
            "ws://127.0.0.1:*", // For local development
        ],
        'frame-src' => [
            "'self'",
            "https://*.firebaseapp.com",
        ],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
    ],
];
