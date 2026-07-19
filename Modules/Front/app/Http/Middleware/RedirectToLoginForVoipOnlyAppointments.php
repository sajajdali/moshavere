<?php

namespace Modules\Front\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Setting\Enum\SettingKeyEnum;
use Symfony\Component\HttpFoundation\Response;

class RedirectToLoginForVoipOnlyAppointments
{
    public function handle(Request $request, Closure $next): Response
    {
        $voipOnly = filter_var(
            setting(SettingKeyEnum::DISABLE_UI_FOR_VOIP_ONLY_APPOINTMENT),
            FILTER_VALIDATE_BOOL,
        );

        if (! $voipOnly || $request->routeIs(
            'front.login.user',
            'front.user.registration',
            'front.logout',
        )) {
            return $next($request);
        }

        if ($request->routeIs('front.homePage') || auth()->guest()) {
            return redirect()->guest(route('front.login.user'));
        }

        return $next($request);
    }
}
