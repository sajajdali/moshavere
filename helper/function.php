<?php
function getCurrentSeason()
{
    $month = \Carbon\Carbon::now()->format('n');

    switch ($month) {
        case 12:
        case 1:
        case 2:
            return 3;
        case 3:
        case 4:
        case 5:
            return 0;
        case 6:
        case 7:
        case 8:
            return 1;
        case 9:
        case 10:
        case 11:
            return 2;
        default:
            return 'Unknown';
    }
}

function generateUniqueCode($length = 4 ,$onlyNumber = false)
{
    if ($onlyNumber){
        $characters = '0123456789';
    } else {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    }

    $code = '';

    // Generate a random code
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}

function dateFormat($date)
{
    return verta($date)->format('d F Y');
}
function dateFormatSimlpe($date)
{
    return verta($date)->format('Y/m/d');
}
function dateFormatComplete($date)
{
    return verta($date)->format('Y/m/d ساعت H:i:s');
}
function appointmentUser()
{
    return app('appointmentUser');
}
function formatBytes($bytes, $precision = 2) {
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;

    if ($bytes < $kilobyte) {
        return $bytes . ' B';
    } elseif ($bytes < $megabyte) {
        return round($bytes / $kilobyte, $precision) . ' KB';
    } elseif ($bytes < $gigabyte) {
        return round($bytes / $megabyte, $precision) . ' MB';
    } else {
        return round($bytes / $gigabyte, $precision) . ' GB';
    }
}
