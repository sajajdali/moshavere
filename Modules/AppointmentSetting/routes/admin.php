<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentSetting\Livewire\GeneralSetting\DoctorList;
use Modules\AppointmentSetting\Livewire\GeneralSetting\GeneralSetting;
use Modules\AppointmentSetting\Livewire\AddAppointment\DocAndSectionList;
use Modules\AppointmentSetting\Livewire\GeneralSetting\SpecialSectionSetting;
use Modules\AppointmentSetting\Livewire\AddAppointment\SpecificDayAvailableAppointment;
use Modules\AppointmentSetting\Livewire\Segment\SegmentCreateOrUpdate;

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
    Route::get('appointment/setting/{user}', GeneralSetting::class)->name('appointment.setting');
    Route::get('appointment/setting/special/section/{user}', SpecialSectionSetting::class)->name('appointment.specialsection');
    Route::get('appointment/setting/{user}/{service}/{place}', GeneralSetting::class)->name('appointment.setting.specialservice');
    //segments
    Route::get('appointment/segment/list', \Modules\AppointmentSetting\Livewire\Segment\SegmentList::class)->name('appointment.segment.list')->can('viewAny', \Modules\AppointmentSetting\app\Models\AppointmentSegment::class);
    Route::get('appointment/segment/create', SegmentCreateOrUpdate::class)->name('appointment.segment.create')->can('create', \Modules\AppointmentSetting\app\Models\AppointmentSegment::class);
    Route::get('appointment/segment/edit/{segment}', SegmentCreateOrUpdate::class)->name('appointment.segment.edit')->can('edit', \Modules\AppointmentSetting\app\Models\AppointmentSegment::class);
    //segments

});
