<?php

if (! function_exists('admin_default_asset')) {
    function admin_default_asset($file): string
    {
        $version = config('app.debug') ? date('Y-m-d H:i:s') : config('app.admin_version');

        return asset('default/admin/' . $file) . '?v=' . md5($version);
    }
}
if (! function_exists('admin_asset')) {
    function admin_asset($file): string
    {
        $version = config('app.debug') ? date('Y-m-d H:i:s') : config('app.admin_version');

        return asset('assets/admin/' . $file) . '?v=' . md5($version);
    }
}
if (! function_exists('holidays_array')) {
    function holidays_array(): array
    {
        return App\Event::whereBetween('date', [now(), now()->addYear()])->where('is_holiday', '1')
            ->get()->map(function ($event) {
                return sprintf('%04d/%02d/%02d', $event->year, $event->month, $event->day);
            })->toArray();
    }
}
if (! function_exists('checkIp')) {
    function checkIp()
    {
        $ip = request()->header('X-Forwarded-For', request()->header('X-Real-Ip', request()->header('ar-real-ip')));
        $ipServer = request()->ip();
        $realIp = $ip == null ? $ipServer : $ip;
        if (config('app.dont_check_ip')) {
            return true;
        }
        if ($realIp == '91.92.122.120' || $realIp == '127.0.0.1' || $realIp == '79.127.12.8') {
            return true;
        } else {
            return false;
        }
    }
}
