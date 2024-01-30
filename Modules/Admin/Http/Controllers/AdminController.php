<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    public function logout()
    {
        auth()->logout();

        return redirect(url('/'));
    }
}
