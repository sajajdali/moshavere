<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventTenancyOnCentralDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $exemptDomains = config('tenancy.exempt_domains', []);

        if (in_array($request->getHost(), $exemptDomains)) {
            // Set a flag to skip tenancy initialization
            app()->instance('tenancy.skipTenantInitialization', true);
        }
        return $next($request);
    }
}
