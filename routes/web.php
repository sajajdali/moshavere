<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    $template = setting(\Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);
    $appointmentUser = \Modules\AppointmentUser\app\Models\AppointmentUser::find(14);
    $notify = $appointmentUser->notify(new \Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification($template));
    dd($notify , "sa");
    return view('welcome');
});
