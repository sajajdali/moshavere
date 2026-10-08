<?php

namespace Modules\Finance\app\Http\Middleware;

use App\Support\TenantModuleAccess;
use Closure;
use Illuminate\Http\Request;

/** the module can be switched off for a customer (tenant) from the central panel */
class EnsureFinanceModuleEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(TenantModuleAccess::enabled('Finance'), 404);

        return $next($request);
    }
}
