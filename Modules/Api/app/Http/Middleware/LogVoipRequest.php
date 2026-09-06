<?php

namespace Modules\Api\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\OnlineConsultation\Models\VoipRequestLog;

class LogVoipRequest
{
    public function handle(Request $request, Closure $next): mixed
    {
        $started = microtime(true);
        $response = $next($request);
        $payload = $response->getData(true);

        VoipRequestLog::create([
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'phone' => $request->input('phone') ?: $request->input('mobile'),
            'client_ip' => $request->ip(),
            'response_status' => $response->getStatusCode(),
            'error_code' => data_get($payload, 'error_code'),
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            'user_agent' => $request->userAgent(),
        ]);

        return $response;
    }
}
