<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentUser\app\Models\AppointmentUser;
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

    Route::post('admin/appointment_user/storevoice', [\Modules\AppointmentUser\Http\Controllers\AppointmentUserController::class, 'upload'])->name('storevoice')->can('viewAny', AppointmentUser::class) ;
    Route::get('test' , [AppointmentUserController::class, 'test'])->name('appointmentuser.test');
    Route::get('appointment/payment/{appointmentUser}', \Modules\AppointmentUser\Livewire\AppointmentUserPayment::class)->name('appointmentUser.payment');

});
