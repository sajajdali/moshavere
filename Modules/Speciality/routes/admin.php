<?php

use Illuminate\Support\Facades\Route;
use Modules\Speciality\app\Models\Speciality;
use Modules\Speciality\Livewire\SpecialityList;
use Modules\Speciality\Livewire\CreateSpeciality;
use Modules\Speciality\Livewire\UpdateOrCreateSpeciality;
use Modules\Speciality\Http\Controllers\SpecialityController;

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
    Route::get('speciality/manage/{speciality?}', UpdateOrCreateSpeciality::class)->name('speciality.manage')->can('create',Speciality::class);
    Route::get('speciality/list', SpecialityList::class)->name('speciality.index')->can('viewAny',Speciality::class);
});
