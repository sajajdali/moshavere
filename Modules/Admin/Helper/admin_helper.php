<?php

if (! function_exists('admin_default_asset')) {
    function admin_default_asset($file): string
    {
        $version = config('app.debug') ? date('Y-m-d H:i:s') : config('app.admin_version');

        return asset('default/admin/'.$file).'?v='.md5($version);
    }
}
if (! function_exists('admin_asset')) {
    function admin_asset($file): string
    {
        $version = config('app.debug') ? date('Y-m-d H:i:s') : config('app.admin_version');

        return asset('assets/admin/'.$file).'?v='.md5($version);
    }
}
if (! function_exists('holidays_array')) {
    function holidays_array(): array
    {
        return App\Event::whereBetween('date',[now(),now()->addYear()])->where('is_holiday', '1')
        ->get()->map(function ($event) {
            return sprintf('%04d/%02d/%02d', $event->year, $event->month, $event->day);
        })->toArray();
    }
}
