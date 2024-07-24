<?php

Route::post('login', 'AuthController@login');
Route::post('verify', 'AuthController@verify');
Route::get('appointment/payment/{appointmentUser}', [Modules\Api\App\Http\Controllers\Payment\paymentController::class, 'createPaymentLink'])->name('api.appointment.payment.create');
Route::get('appointment/payment/callback/{appointmentUser}', [Modules\Api\App\Http\Controllers\Payment\paymentController::class, 'callback'])->name('api.appointment.payment.callback');
// payment
