<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;

class ShortLinkController extends Controller
{
    public function index($param)
    {
        $shortLink = ShortLink::firstWhere('link_code', $param);
        abort_unless($shortLink, 404);

        $shortLink->increment('number_visited', 1, ['visited_at' => now()]);

        $target = $shortLink->targetUrl();

        // لینک های قدیمی آدرس کامل دارند و لینک های جدید روی همین دامنه ساخته می شوند
        return str_starts_with($target, 'http') ? redirect()->away($target) : redirect()->to($target);
    }
}
