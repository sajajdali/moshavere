<?php

use Illuminate\Support\Facades\Route;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Livewire\Admin\AppointmentUserList;
use Modules\AppointmentUser\Livewire\Admin\AppointmentStatusLogList;
use Modules\AppointmentUser\Livewire\Admin\Online\MessageDetail;
use Modules\AppointmentUser\Livewire\Admin\FeedBack\Feedbackindex;
use Modules\AppointmentUser\Livewire\Admin\FeedBack\FeedbackFormCreate;
use Modules\AppointmentUser\Livewire\Admin\FeedBack\FeedbackFormList;
use Modules\AppointmentUser\Livewire\Admin\FeedBack\FeedbackAnswerList;
use Modules\AppointmentUser\Livewire\Admin\AppointmentUserCreateOrUpdate;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\ListOfAvailableDay;
use Modules\AppointmentUser\Livewire\Admin\Online\AppointmentOnlineMessagesList;
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
    Route::get('appointment_user/list', AppointmentUserList::class)->name('appointment_user.list')->middleware(['appointment_user_list_middlewere']);
    Route::get('appointment_user/status-logs', AppointmentStatusLogList::class)->name('appointment_user.status_logs')->middleware('can:SUPER_ADMIN');
    Route::get('appointment_user/create', AppointmentUserCreateOrUpdate::class)->name('appointment_user.addApp')->middleware('can:appointment_user.addApp');
    Route::get('appointment_user/edit/{appointment_user}', AppointmentUserCreateOrUpdate::class)->name('appointment_user.edit')->can('edit', AppointmentUser::class);
    Route::get('appointment_user/Online/message/list', AppointmentOnlineMessagesList::class)->name('appointment_user.message.list')->middleware('can:appointment_user.message');
    Route::get('appointment/feedback', Feedbackindex::class)->name('appointment.feedback')->middleware('can:appointment_user.feedBack');
    Route::get('appointment/feedback/forms', FeedbackFormList::class)->name('appointment.feedback.forms')->middleware('can:appointment_user.feedBack');
    Route::get('appointment/feedback/answers', FeedbackAnswerList::class)->name('appointment.feedback.answers')->middleware('can:appointment_user.feedBack');
    Route::get('appointment/feedback/create', FeedbackFormCreate::class)->name('appointment.feedback.create')->middleware('can:appointment_user.feedBack');
    Route::get('appointment_user/Online/message/detail/{onlineAppId}', MessageDetail::class)->name('appointment_user.message.detail');
    Route::get('appointment/add/specificday/{serviceId}/{placeId}/{appId}/{date}', SpecificDayAvailableAppointment::class)->name('appointment.add.specificday');
    Route::get('appointment/add/{doctorId}/{sectionId}/{placeId}', ListOfAvailableDay::class)->name('appointment.add.setTime');
});

