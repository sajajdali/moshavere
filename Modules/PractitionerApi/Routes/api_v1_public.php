<?php

use Illuminate\Support\Facades\Route;
use Modules\PractitionerApi\Http\Controllers\V1\Auth\RequestOtpController;
use Modules\PractitionerApi\Http\Controllers\V1\Auth\VerifyOtpController;
use Modules\PractitionerApi\Http\Controllers\V1\System\StatusController;
use Modules\PractitionerApi\Http\Controllers\V1\System\LandingContentController;

Route::get('status', StatusController::class)->name('status');
Route::get('landing/content', LandingContentController::class)->name('landing.content');

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('otp/request', RequestOtpController::class)
        ->middleware('throttle:practitioner-otp-request')
        ->name('otp.request');

    Route::post('otp/verify', VerifyOtpController::class)
        ->middleware('throttle:practitioner-otp-verify')
        ->name('otp.verify');
});
