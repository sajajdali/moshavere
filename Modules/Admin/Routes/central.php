<?php

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::get('/centralLogin', function () {
//    $tenantDb = DB::connection()->getDatabaseName();
    $User = \Modules\User\Entities\User::find(1);
    \Illuminate\Support\Facades\Auth::loginUsingId(request()->get('id', $User->id));
    //     if (auth()->check()) {

    return redirect()->route('central.dashboard');

});
Route::prefix('central')
    ->middleware(['web', 'admin','prevent-tenant'])->as('central.')->group(function () {
        Route::get('dashboard', \Modules\Admin\Livewire\Central\CentralDashboard::class)->name('dashboard');


        Route::get('new_site', \Modules\Admin\Livewire\Central\NewSiteCreateOrUpdate::class)->name('new_site.index');
        Route::get('new_site/create', \Modules\Admin\Livewire\Central\NewSiteCreateOrUpdate::class)->name('new_site.create');
        Route::get('new_site/edit/{new_site}', \Modules\Admin\Livewire\Central\NewSiteCreateOrUpdate::class)->name('new_site.edit');
        Route::get('customers', \Modules\Admin\Livewire\Central\CustomerManager::class)->name('customers.index');
        Route::get('renewal-settings', \Modules\Admin\Livewire\Central\RenewalSettings::class)->name('renewal-settings');

    });
