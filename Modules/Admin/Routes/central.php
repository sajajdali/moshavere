<?php

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::get('/centralLogin', function () {
//    $tenantDb = DB::connection()->getDatabaseName();
    $User = \Modules\User\Entities\User::find(1);
    \Illuminate\Support\Facades\Auth::loginUsingId(request()->get('id', $User->id));
    //     if (auth()->check()) {
    return redirect()->route('admin.dashboard');

});
Route::prefix('central')
    ->middleware(['web', 'admin'])->as('admin.')->group(function () {
        Route::get('dashboard', \Modules\Admin\Livewire\Central\CentralDashboard::class)->name('dashboard');
    });
