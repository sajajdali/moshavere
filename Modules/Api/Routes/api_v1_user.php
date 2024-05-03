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

Route::post('logout', 'AuthController@logout');
//Route::get('me', 'UserController@me');
Route::get('notification', 'UserController@notification');
Route::post('notification', 'UserController@notificationRead');
Route::post('notification/read-all', 'UserController@notificationReadAll');
Route::post('edit', 'UserController@edit');

Route::prefix('profile')->group(function () {
    Route::get('dashboard', [\Modules\Api\Http\Controllers\Profile\DashboardController::class, 'index'])->name('dashboard');
    Route::get('appointments', [\Modules\Api\Http\Controllers\Profile\DashboardController::class, 'appointmentList'])->name('appointment_list');

    Route::prefix('chat')->group(function () {
        Route::get('index' , [\Modules\Api\Http\Controllers\ChatController::class , 'index']);
        Route::get('list' , [\Modules\Api\Http\Controllers\ChatController::class , 'list']);
        Route::get('show/{chat}' , [\Modules\Api\Http\Controllers\ChatController::class , 'show']);
        Route::post('send_message' , [\Modules\Api\Http\Controllers\ChatController::class , 'sendMessage']);
    });
});
Route::get('test', function () {
    $user = auth()->user();
    $user->notify(new \Modules\User\Notifications\UserMessageNotification(
        title: "test title",
        excerpt: "test excerpt",
        message: 'test message',
    ));
});

// appointment
Route::prefix('appointment')->group(function () {
    Route::get('doctors_list', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'doctorsList'])->name('api.appointment.doctor_list');
    Route::get('doctor/{doctor}', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'doctorProfile'])->name('api.appointment.doctor_profile');
    Route::get('services/{doctor}', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'services'])->name('api.appointment.services');
    Route::get('places/{doctor}', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'places'])->name('api.appointment.places');
    Route::get('list_days', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'listDays'])->name('api.appointment.list_days');
    Route::post('store', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'store'])->name('api.appointment.store');
    Route::get('tracking/{appointmentUser}', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'tracking'])->name('api.appointment.tracking');

    Route::get('user_info', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiController::class, 'userInfo'])->name('api.user_info');
    //
    Route::prefix('online')->group(function () {
        Route::get('messages/{appointmentOnline}', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiOnlineController::class, 'messages'])->name('api.messages');
        Route::post('send_message/{appointmentOnline}', [\Modules\Api\Http\Controllers\Appointment\AppointmentApiOnlineController::class, 'sendMessage'])->name('api.send_message');
    });
});

// transactions
Route::prefix('transaction')->group(function () {
    Route::get('list', [\Modules\Api\Http\Controllers\TransactionController::class, 'index']);
});

