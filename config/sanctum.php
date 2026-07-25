<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [
    // StockManager deliberately uses personal access tokens, not SPA cookies.
    'stateful' => [],
    'guard' => [],
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 480),
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'stockmanager_'),
    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],
];
