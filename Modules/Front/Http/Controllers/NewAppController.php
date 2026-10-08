<?php

namespace Modules\Front\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Api\Http\Controllers\Tenant\TenantAppController;

/**
 * صفحه پایه اپ جدید (React).
 * تمام مسیرهای سمت کاربر به این صفحه می رسند و مسیریابی در سمت مرورگر انجام می شود.
 */
class NewAppController extends Controller
{
    public function __invoke(TenantAppController $api)
    {
        // همان داده ای که endpoint بوت استرپ می دهد، تا اپ بدون درخواست اضافه بالا بیاید
        $bootstrap = $api->bootstrap()->getData(true);

        return view('front::newapp.shell', ['bootstrap' => $bootstrap]);
    }
}
