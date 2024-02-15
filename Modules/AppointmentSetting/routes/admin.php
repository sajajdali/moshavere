<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentSetting\Livewire\Absentee\AbsenteeList;
use Modules\AppointmentSetting\Livewire\GeneralSetting\DoctorList;
use Modules\AppointmentSetting\Livewire\Absentee\AbsenteeRegistration;
use Modules\AppointmentSetting\Livewire\GeneralSetting\GeneralSetting;
use Modules\AppointmentSetting\Livewire\AddAppointment\DocAndSectionList;
use Modules\AppointmentSetting\Livewire\GeneralSetting\SpecialSectionSetting;
use Modules\AppointmentSetting\Livewire\AddAppointment\ListOfAvailableAppointment;
use Modules\AppointmentSetting\Livewire\AddAppointment\SpecificDayAvailableAppointment;

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
    Route::get('appointment/setting/doctors', DoctorList::class)->name('appointment.doctor.list');
    Route::get('appointment/setting/absentee', AbsenteeRegistration::class)->name('appointment.absentee');
    Route::get('appointment/setting/absentee/list', AbsenteeList::class)->name('appointment.absentee.list');
    Route::get('appointment/setting/{user}', GeneralSetting::class)->name('appointment.setting');
    Route::get('appointment/setting/special/section/{user}', SpecialSectionSetting::class)->name('appointment.specialsection');
    //add appointment
    Route::get('appointment/section/list', DocAndSectionList::class)->name('appointment.add.sectionList');
    Route::get('appointment/add/specificday/{date}', SpecificDayAvailableAppointment::class)->name('appointment.add.specificday');
    Route::get('appointment/add/{doctorId}/{sectionId}', ListOfAvailableAppointment::class)->name('appointment.add.setTime');
});
