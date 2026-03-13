<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Http\Controllers\FrontController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::get('/payment/saman/{token}', function ($token) {
        return view('payment.saman-form', ['token' => $token]);
    })->name('payment.saman.form');
    Route::resource('front', FrontController::class)->names('front');
});
