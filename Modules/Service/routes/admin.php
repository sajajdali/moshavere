<?php

use Illuminate\Support\Facades\Route;
use Modules\Service\Livewire\ServiceList;
use Modules\Service\Livewire\CreateOrUpdate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::get('service/craete', CreateOrUpdate::class)->name('service.create')->can('create', \Modules\Place\app\Models\Service::class);
    Route::get('service/edit/{service}', CreateOrUpdate::class)->name('service.edit')->can('viewAny', \Modules\Place\app\Models\Service::class);
    Route::get('service/list', ServiceList::class)->name('service.list')->can('viewAny', \Modules\Place\app\Models\Service::class);

});
