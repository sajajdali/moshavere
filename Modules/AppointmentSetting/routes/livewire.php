<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentSetting\Livewire\DoctorList;
use Modules\AppointmentSetting\Livewire\GeneralSetting;

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
    Route::get('appointment/setting/doctors', DoctorList::class)->name('appointment.doctor.list');
    Route::get('appointment/setting/{user}', GeneralSetting::class)->name('appointment.setting');
});
