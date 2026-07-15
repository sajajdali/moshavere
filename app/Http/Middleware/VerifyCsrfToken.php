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
        'appointment/detail/*',
        'admin/tenant/renew/callback',
        'api/v1/VoIP/send_custom_link',
        'api/v1/VoIP/send_custom_link_2',
    ];
}
