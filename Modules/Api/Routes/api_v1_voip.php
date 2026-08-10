<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// appointment
Route::prefix('voip')->group(function () {
    Route::get('list_days' , [\Modules\Api\Http\Controllers\Voip\VoipController::class , 'listDays'] )->name('api.voip.list_days');
    Route::get('appointment/store' , [\Modules\Api\Http\Controllers\Voip\VoipController::class , 'storeAppointment'] )->name('api.voip.storeAppointment');
});
// appointment

Route::prefix('VoIP')->group(function () {
    Route::get('doctor_appointment', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'checkDoctorAppointment']);
    Route::get('appointment_doctors', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'getAppointmentDoctors']);
    Route::get('appointment_offices_parts', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'getAppointmentOfficesAndParts']);
    Route::get('check_appointment', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'checkAppointment']);
    Route::get('appointment_times', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'getAppointmentTimes']);
    Route::post('appointment', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'storeAppointment']);
    Route::get('appointment_user', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'getAppointmentUser']);
    Route::get('cancel_appointment_user', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'cancelAppointmentUser']);
    Route::get('online_visit', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'onlineVisit']);
    Route::get('connect_to_operator', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'connectToOperator']);
    Route::get('incoming_call', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'incomingCall']);
    Route::post('payment_send_second_password', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'paymentSendSecondPassword']);
    Route::post('payment_by_voip', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'paymentByVoip']);
    Route::post('send_custom_link', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'sendCustomLink'])
        ->name('api.voip.send_custom_link');
    Route::post('send_custom_link_2', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'sendCustomLink2'])
        ->name('api.voip.send_custom_link_2');
    Route::post('store_survey', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'storeSurvey']);
    Route::post('voice_record', [\Modules\Api\Http\Controllers\Voip\VoipController::class, 'storeVoiceRecord'])
        ->name('api.voip.voice_record');
});
