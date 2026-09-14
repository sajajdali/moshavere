<?php

namespace Modules\PractitionerApi\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePractitionerApi
{
    public function __construct(private readonly Auth $auth)
    {
    }

    /**
     * Authenticate a mobile request with its Sanctum bearer token.
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->auth->guard('sanctum')->check()) {
            throw new AuthenticationException('Unauthenticated.', ['sanctum']);
        }

        $this->auth->shouldUse('sanctum');

        return $next($request);
    }
}
