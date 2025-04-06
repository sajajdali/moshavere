<?php

namespace Modules\AppointmentUser\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentUserListmiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {

        if (!Gate::any(['appointment_user.list', 'appointment_user.online','appointment_user.own'])) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
}
