<?php

use Illuminate\Support\Facades\Route;
use Modules\Api\Http\Controllers\Tenant\TenantAppController;

/*
|--------------------------------------------------------------------------
| Tenant App API (اپ جدید React)
|--------------------------------------------------------------------------
|
| این مسیرها فقط برای اپ جدید سمت کاربر استفاده می شوند و با session
| احراز هویت می شوند (نه توکن)، چون اپ در همان دامنه اجرا می شود.
|
*/

Route::prefix('tenant')->group(function () {
    Route::get('bootstrap', [TenantAppController::class, 'bootstrap']);
    Route::get('home', [TenantAppController::class, 'home']);
    Route::get('about-us', [TenantAppController::class, 'aboutUs']);
    Route::get('contact-us', [TenantAppController::class, 'contactUs']);
    Route::post('contact-us', [TenantAppController::class, 'sendContactMessage'])
        ->middleware('throttle:10,1');
    Route::get('search', [TenantAppController::class, 'search']);
    // پزشکان ارائه دهنده یک خدمت؛ برای وقتی مودال رزرو فقط با شناسه خدمت باز می شود
    Route::get('service/{service}/doctors', [TenantAppController::class, 'serviceDoctors']);
    Route::get('doctor/{doctor}', [TenantAppController::class, 'doctor']);
    // گزینه های رزرو: مطب، بخش، ناحیه و اپراتور
    Route::get('doctor/{doctor}/booking-options', [TenantAppController::class, 'bookingOptions']);
    Route::get('doctor/{doctor}/days', [TenantAppController::class, 'doctorDays']);

    // ورود بیمار با کد پیامکی؛ محدودیت نرخ برای جلوگیری از ارسال انبوه
    Route::post('auth/login', [TenantAppController::class, 'login'])
        ->middleware('throttle:8,1');
    Route::post('auth/verify', [TenantAppController::class, 'verify'])
        ->middleware('throttle:12,1');
    Route::post('auth/admin-login', [TenantAppController::class, 'adminLogin'])
        ->middleware('throttle:6,1');

    // جزئیات نوبت با کد پیگیری؛ مانند صفحه قبلی بدون نیاز به ورود.
    // محدودیت نرخ برای جلوگیری از حدس زدن کدهای پیگیری
    Route::get('appointment/{tracking_code}', [TenantAppController::class, 'appointment'])
        ->where('tracking_code', '[A-Za-z0-9_-]+')
        ->middleware('throttle:30,1');

    Route::middleware('auth')->group(function () {
        Route::post('auth/logout', [TenantAppController::class, 'logout']);
        Route::post('auth/register', [TenantAppController::class, 'completeRegistration'])
            ->middleware('throttle:10,1');

        Route::get('profile', [TenantAppController::class, 'profile']);
        Route::post('profile', [TenantAppController::class, 'updateProfile'])
            ->middleware('throttle:20,1');

        // لغو نوبت توسط بیمار
        Route::post('appointment/{tracking_code}/cancel', [TenantAppController::class, 'cancelAppointment'])
            ->where('tracking_code', '[A-Za-z0-9_-]+')
            ->middleware('throttle:10,1');

        // ثبت نوبت پس از تایید بیمار در مودال انتخاب ساعت
        Route::post('doctor/{doctor}/appointment', [TenantAppController::class, 'storeAppointment'])
            ->middleware('throttle:10,1');

        // گفتگوی نوبت آنلاین
        Route::get('chat/{onlineAppointmentId}', [TenantAppController::class, 'chatShow'])
            ->whereNumber('onlineAppointmentId');
        Route::post('chat/{onlineAppointmentId}/send', [TenantAppController::class, 'chatSend'])
            ->whereNumber('onlineAppointmentId')
            ->middleware('throttle:30,1');
    });
});
