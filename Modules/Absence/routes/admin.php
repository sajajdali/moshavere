<?php

use Illuminate\Support\Facades\Route;
use Modules\Absence\Livewire\AbsenceList;
use Modules\Absence\Livewire\AbsenceRegistration;

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
    Route::get('appointment/setting/absence', AbsenceRegistration::class)->name('absence.create');
    Route::get('appointment/setting/absence/list', AbsenceList::class)->name('absence.list');
});
