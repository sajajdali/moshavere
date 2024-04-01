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
});
// appointment

