<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use Illuminate\Http\Request;

class ShortLinkController extends Controller
{
    public function index(Request $request)
    {
        $param = $request->get('param');
        $shortLink = ShortLink::firstWhere('link_code', $param);
        if ($shortLink->isNotEmpty()) {
            $number_visited_old =  $shortLink->$shortLink ?? 0;
            $shortLink->update([
                'number_visited' => $number_visited_old + 1,
                'visited_at' => \now(),
            ]);
            return redirect()->url($shortLink->link_url);
        } else {
            return abort(404);
        }
    }
}
