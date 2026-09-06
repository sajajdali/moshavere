<?php

namespace Modules\OnlineConsultation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class EnsureConsultationEnabled
{
    public function handle(Request $request, Closure $next): mixed
    {
        abort_unless(ConsultationAccess::enabled(), 404);

        return $next($request);
    }
}
