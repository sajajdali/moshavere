<?php

use Illuminate\Support\Facades\Route;
use Modules\Front\Livewire\Appointment\AppointmentDetail;

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
    Route::get('appointment/detail/{tracking_code}', AppointmentDetail::class)->name('front.appointment.detail');
});
