<?php

namespace Modules\Api\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Api\Http\Controllers\Voip\VoipResponseCode;

class BasicAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $authUser = (string) setting(SettingKeyEnum::VOIP_USERNAME);
        $authPassword = (string) setting(SettingKeyEnum::VOIP_PASSWORD);
        $suppliedUser = (string) $request->getUser();
        $suppliedPassword = (string) $request->getPassword();

        if (
            $suppliedUser === '' ||
            $suppliedPassword === '' ||
            ! hash_equals($authUser, $suppliedUser) ||
            ! hash_equals($authPassword, $suppliedPassword)
        ) {
            return response()->json([
                'status' => false,
                'error_code' => VoipResponseCode::UNAUTHORIZED,
                'message' => 'Unauthorized',
            ], 401, [
                'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
                'WWW-Authenticate' => 'Basic realm="Access denied"',
            ]);
        }

        return $next($request);
    }
}
