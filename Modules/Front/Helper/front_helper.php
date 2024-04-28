<?php

if (! function_exists('front_asset')) {
    function front_asset($file): string
    {
        $version = config('app.debug') ? date('Y-m-d H:i:s') : config('app.admin_version');

        return asset('assets/front/'.$file).'?v='.md5($version);
    }
}
