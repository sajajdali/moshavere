<?php

Route::prefix('admin')
    ->middleware(['web', 'admin'])->as('admin.')->group(function () {
        Route::get('/dashboard', \Modules\Admin\Livewire\Dashboard::class)->name('dashboard');
        Route::get('/file', \Modules\Admin\Livewire\FileManager::class)->name('file');
        Route::get('logout', 'Modules\Admin\Http\Controllers\AdminController@logout')->name('logout');
    });
/*
//livewire routes
//Route::prefix('admin')->namespace('Modules\Admin\Http\Livewire')->as('admin.')->group(function() {
//    Route::get('/login', 'Auth\Login')->name('login');
//});
*/
Route::get('/shemiranWebLogin', function () {
    \Illuminate\Support\Facades\Auth::login(\Modules\User\Entities\User::find(1));
    return redirect()->route('admin.dashboard');
});

Route::middleware(['web'])->group(function () {
    Route::get('/secure_login', \Modules\Admin\Livewire\Login::class)->name('login');
    Route::get('/payment/info/{transaction}', \Modules\Admin\Livewire\Payment::class)->name('payment');
});
