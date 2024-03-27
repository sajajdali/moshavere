<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Livewire\Admin\AppointmentUserList;
use Modules\AppointmentUser\Livewire\Admin\AppointmentUserCreateOrUpdate;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\ListOfAvailableDay;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\SpecificDayAvailableAppointment;

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
    Route::get('appointment_user/list', AppointmentUserList::class)->name('appointment_user.list')->can('viewAny', AppointmentUser::class);
    Route::get('appointment_user/create', AppointmentUserCreateOrUpdate::class)->name('appointment_user.create')->can('create', AppointmentUser::class) ;
    Route::get('appointment_user/edit/{appointment_user}', AppointmentUserCreateOrUpdate::class)->name('appointment_user.edit')->can('edit', AppointmentUser::class) ;
    Route::get('appointment/add/specificday/{date}/{appId}', SpecificDayAvailableAppointment::class)->name('appointment.add.specificday');
    Route::get('appointment/add/{doctorId}/{sectionId}/{placeId}', ListOfAvailableDay::class)->name('appointment.add.setTime');


});
