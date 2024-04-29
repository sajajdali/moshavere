<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    public function __construct(protected ?RateLimiter $limiter)
    {
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $maxAttempts = 5, $decayMinutes = 1)
    {
        $key = $request->ip(); // You can customize the key based on your needs

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'تعداد درخواست فرستاده شده زیاد است ، لطفا بعدا امتحان کنید!'
            ], 429);
        }
        $this->limiter->hit($key, $decayMinutes);

        return $next($request);
    }
}
