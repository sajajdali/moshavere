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
});
Route::get('test', function () {
    $user = auth()->user();
    $user->notify(new \Modules\User\Notifications\UserMessageNotification(
        title: "test title",
        excerpt: "test excerpt",
        message: 'test message',
    ));
});
