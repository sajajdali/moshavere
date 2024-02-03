<?php

use Illuminate\Support\Facades\Route;
use Modules\Speciality\Http\Controllers\SpecialityController;
use Modules\Speciality\Livewire\CreateSpeciality;
use Modules\Speciality\Livewire\SpecialityList;
use Modules\Speciality\Livewire\UpdateOrCreateSpeciality;

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
    Route::get('speciality/manage/{speciality?}', UpdateOrCreateSpeciality::class)->name('speciality.manage');
    Route::get('speciality/list', SpecialityList::class)->name('speciality.index');
});
