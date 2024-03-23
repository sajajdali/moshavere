<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentUser\Http\Controllers\AppointmentUserController;

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
    Route::resource('appointmentuser', AppointmentUserController::class)->names('appointmentuser');

    Route::get('test' , [AppointmentUserController::class, 'test'])->name('appointmentuser.test');
});
