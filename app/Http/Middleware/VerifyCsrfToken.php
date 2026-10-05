<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Add all API routes that need to bypass CSRF
        '/api/login',
        '/api/logout',
        '/api/forgot-password',
        '/api/reset-password',
        '/api/check-token-reset-password',
        // Or simply exclude all API routes (not recommended for production)
        // '/api/*'
    ];
}