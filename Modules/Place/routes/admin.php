<?php

use Illuminate\Support\Facades\Route;
use Modules\Place\Livewire\Create;
use Modules\Place\Livewire\CreateOrUpdate;
use Modules\Place\Livewire\Index;

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
    Route::get('place/index' , \Modules\Place\Livewire\PlaceList::class)->name('place.index')->can('viewAny' , \Modules\Place\app\Models\Place::class);
    Route::get('place/create', CreateOrUpdate::class)->name('place.create')->can('create' , \Modules\Place\app\Models\Place::class);
    Route::get('place/edit/{place}', CreateOrUpdate::class)->name('place.edit')->can('viewAny' , \Modules\Place\app\Models\Place::class);
    Route::get('place/list', Index::class)->name('place.list');
});
