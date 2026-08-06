<?php

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::prefix('admin')
    ->middleware(['web', 'admin'])->as('admin.')->group(function () {
        Route::get('/dashboard', \Modules\Admin\Livewire\Dashboard::class)->name('dashboard');
        Route::get('/incoming-calls', \Modules\Admin\Livewire\IncomingCallList::class)->name('incoming-calls');
        Route::get('/tenant/renew', \Modules\Admin\Livewire\TenantRenew::class)->name('tenant-renew');
        Route::get('/file', \Modules\Admin\Livewire\FileManager::class)->name('file');
        Route::get('logout', 'Modules\Admin\Http\Controllers\AdminController@logout')->name('logout');
    });

Route::post('/admin/tenant/renew/callback', \Modules\Admin\Http\Controllers\TenantRenewCallbackController::class)
    ->name('admin.tenant-renew.callback');
/*
//livewire routes
//Route::prefix('admin')->namespace('Modules\Admin\Http\Livewire')->as('admin.')->group(function() {
//    Route::get('/login', 'Auth\Login')->name('login');
//});
*/

Route::get('/shemiranWebLogin', function () {
    //     \Illuminate\Support\Facades\Auth::login(\Modules\User\Entities\User::find(1));
    //     return redirect()->route('admin.dashboard');
    if (checkIp()) {
        $User = \Modules\User\Entities\User::find(1);
        \Illuminate\Support\Facades\Auth::loginUsingId(request()->get('id', $User->id));
        if (auth()->check()) {
            return redirect()->route('admin.dashboard');
        }
    } else {
        return abort(401);
    }


    // return redirect()->route('login');
});
Route::get('/login_as/{id}', function ($id) {
    //     \Illuminate\Support\Facades\Auth::login(\Modules\User\Entities\User::find(1));
    //     return redirect()->route('admin.dashboard');
    if (checkIp()) {
        $User = \Modules\User\Entities\User::find($id);
        \Illuminate\Support\Facades\Auth::loginUsingId(request()->get('id', $User->id));
        if (auth()->check()) {
            return redirect()->route('admin.dashboard');
        }
    } else {
        return abort(401);
    }


    // return redirect()->route('login');
});


Route::middleware(['web'])->group(function () {
    // Route::get('/secure_login', \Modules\Admin\Livewire\Login::class)->name('login');
    Route::get('/payment/info/{transaction}', \Modules\Admin\Livewire\Payment::class)->name('payment');
});
