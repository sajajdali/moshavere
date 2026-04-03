<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (!checkIp() && env('MAINTENANCE_MODE', false)) {
            return response()->view('maintains');
        }
        return $next($request);
    }
}
