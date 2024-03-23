<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentSetting\Livewire\GeneralSetting\DoctorList;
use Modules\AppointmentSetting\Livewire\GeneralSetting\GeneralSetting;
use Modules\AppointmentSetting\Livewire\AddAppointment\DocAndSectionList;
use Modules\AppointmentSetting\Livewire\GeneralSetting\SpecialSectionSetting;
use Modules\AppointmentSetting\Livewire\AddAppointment\ListOfAvailableAppointment;
use Modules\AppointmentSetting\Livewire\AddAppointment\SpecificDayAvailableAppointment;
use Modules\AppointmentSetting\Livewire\Segment\SegmentCreateOrUpdate;
use Modules\AppointmentSetting\Livewire\UserAppointMentList\Index;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Livewire\Admin\AppointmentUserCreateOrUpdate;
use Modules\AppointmentUser\Livewire\Admin\AppointmentUserList;

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
});
