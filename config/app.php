<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'FMMP'),
    'url' => env('APP_URL', 'http://localhost/fitcoch/public'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', 'true') === 'true',
    'jwt_secret' => env('JWT_SECRET', 'change-this-to-a-random-64-char-secret-in-production'),
    'jwt_ttl' => (int) env('JWT_TTL', '3600'),
    'session_lifetime' => (int) env('SESSION_LIFETIME', '1800'),
    'version' => '1.0.0',
];
