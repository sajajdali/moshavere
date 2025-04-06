<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Store the intended URL in session if the request is not expecting JSON
        if (!$request->expectsJson()) {
            Session::put('url.intended', url()->full());
            return route('front.login.user');
        }

        return null;
    }
}
