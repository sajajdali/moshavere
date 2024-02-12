<?php

if (!function_exists('ip')) {
    function ip(): ?string
    {
        if (request()->headers->has('AR_REAL_IP')) {
            return request()->headers->get('AR_REAL_IP');
        }
        if (request()->headers->has('HTTP_X_FORWARDED_FOR')) {
            return request()->headers->get('HTTP_X_FORWARDED_FOR');
        }
        return request()->ip();
    }
}

if (!function_exists('userRoute')) {
    function userRoute($user, $step = null)
    {
        if ($step == null) {
            return $user->route ?? \Modules\User\Enum\UserRouteEnum::getDefault();
        } else {
            $user->metas()->updateOrCreate(['meta_key' => \Modules\User\Enum\UserMetaEnum::ROUTE], [
                'meta_key' => \Modules\User\Enum\UserMetaEnum::ROUTE,
                'meta_value' => $step
            ]);
            return $step;
        }
    }
}


if (!function_exists('urlPublic')) {
    function urlPublic($url): string
    {
        return env('APP_URL') . '/'. $url;
    }
}
