<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Setting\Enum\SettingKeyEnum;
use Symfony\Component\HttpFoundation\Response;

class StaticBasicAuth
{

    public function handle(Request $request, Closure $next)
    {
        $username = setting(SettingKeyEnum::API_USERNAME);
        $password = setting(SettingKeyEnum::API_PASSWORD);

        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
