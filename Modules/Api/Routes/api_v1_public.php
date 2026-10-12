<?php

Route::post('login', 'AuthController@login');
Route::post('verify', 'AuthController@verify');
// پرداخت نوبت (قالب جدید): تننت باید پیش از بارگذاری نوبت و تنظیمات درگاه مقداردهی شود.
// بانک ها (سداد، سامان) نتیجه را با POST به callback برمیگردانند.
Route::middleware(\Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class)->group(function () {
    Route::get('appointment/payment/{appointmentUser}', [Modules\Api\app\Http\Controllers\Payment\PaymentController::class, 'createPaymentLink'])->name('api.appointment.payment.create');
    Route::match(['get', 'post'], 'appointment/payment/callback/{appointmentUser}', [Modules\Api\app\Http\Controllers\Payment\PaymentController::class, 'callback'])->name('api.appointment.payment.callback');
});
// payment
