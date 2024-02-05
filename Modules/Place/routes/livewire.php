<?php

use Illuminate\Support\Facades\Route;
use Modules\Place\Http\Controllers\PlaceController;
use Modules\Place\Livewire\Create;

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
    Route::get('place/create', Create::class)->name('place.create');
});
