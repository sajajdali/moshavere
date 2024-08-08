<?php

use Carbon\Carbon;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\App;
use Modules\Place\app\Models\Place;
use Illuminate\Support\Facades\Route;
use Modules\Chat\app\Models\ChatDetail;
use Modules\Setting\Enum\SettingKeyEnum;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\ShortLinkController;
use Modules\Reminder\Enum\ReminderStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\Reminder\Enum\ReminderParametersEnum;
use Modules\Reminder\app\Models\AppointmentReminder;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSegmentItem;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsReminder;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    echo ('coming soon :D ');
    // $template = setting(\Modules\Setting\Enum\SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);
    // $appointmentUser = \Modules\AppointmentUser\app\Models\AppointmentUser::find(14);
    // $notify = $appointmentUser->notify(new \Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification($template));
    // dd($notify, "sa");
    // return view('welcome');
});
Route::get('/s/{param}', [ShortLinkController::class, 'index']);

Route::get('/pusher', [\App\Http\Controllers\PusherController::class, 'index']);
Route::get('/pusher-r', [\App\Http\Controllers\PusherController::class, 'indexr']);
Route::post('/broadcast', [\App\Http\Controllers\PusherController::class, 'broadcast']);
Route::post('/receive', [\App\Http\Controllers\PusherController::class, 'receive']);

Route::get('pusher-test/{chat}', function ($chat) {

    $chatDetail = \Modules\Chat\app\Models\ChatDetail::find($chat);
    $message = \Modules\Api\app\Resources\Api\Chat\ChatDetailResource::make($chatDetail);
    event(new App\Events\PusherBroadcast($message, $chat));
    return "Event has been sent!";
});
Route::get('ttt', function () {

    // $setting = AppointmentSetting::find(2);
    // $listOfAppointment = app('AppointmentUserService')->listAppointments($setting);

    // $firstTwoEmpty = [];
    // $report = $listOfAppointment['report'];
    // $mainDaActive = $report['min_day_active'];
    // $isDay   = verta()->addDays($mainDaActive)->day;
    // $isMonth = verta()->addDays($mainDaActive)->month;
    // $isYear  = verta()->addDays($mainDaActive)->year;

    // $result = [];
    // $maxDay = 50;
    // $DaysDisplayed = 0;


    // foreach ($listOfAppointment['data'] as $yeay => $monthWithAppointment) {
    //     if ($yeay < $isYear) {
    //         continue;
    //     }
    //     foreach ($monthWithAppointment as $month => $appointments) {

    //         foreach ($appointments as $day => $appointment) {

    //             if ($day < $isDay && $month < $isMonth && $yeay < $isYear) {
    //                 continue;
    //             }
    //             // if (isset($lastDayActive) && $lastDayActive != null) {
    //             //     if ($appointment['day_number_gmt'] <= $lastDayActive) {
    //             //         continue;
    //             //     }
    //             // }
    //             if ($appointment['empty_appoints'] <= 0 || $appointment['status'] == false ||   $appointment['user_status'] == false) {
    //                 continue;
    //             }
    //             $dayNumber = $appointment['day_number_gmt'];
    //             if ($DaysDisplayed > $maxDay) {
    //                 break 3;
    //             }
    //             $DaysDisplayed++;
    //             foreach ($appointment['times'] as $increment =>  $time) {
    //                 if ($time['status']) {
    //                     $result[$dayNumber][] = [
    //                         'status' => true,
    //                         'day_of_week_name' =>  verta($time['timestamp'])->formatDifference(),
    //                         'day_name'            =>  verta($time['timestamp'])->format('l'),
    //                         'date_of_month'    =>  verta($time['timestamp'])->format('%d %B'),
    //                         'time_stamp' => Carbon::parse($time['timestamp'])->setTimeFromTimeString($time['from'])->timestamp,
    //                         'from' => substr($time['from'], 0, 5),
    //                         'until' => substr($time['until'], 0, 5),
    //                     ];
    //                 } else {
    //                     if (setting(\Modules\Setting\Enum\SettingKeyEnum::SHOW_FALSE_APPOINTMENT_STATUS)) {
    //                         $result[$dayNumber][] = [
    //                             'status' => false,
    //                             'from' => $time['from'],
    //                         ];
    //                     }
    //                 }
    //             }
    //         }
    //     }
    // }
    // $user = User::find(4);
    // // If he wants to take the appointmnet for someone else
    // $someoneModel = null;
    // $foHimself = 1;
    // // main user data
    // $mainUser = new UserModel(
    //     user: $user,
    //     firstName: $user->first_name,
    //     lastName: $user->last_name,
    // );
    // $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel);

    // foreach ($result as $date => $appointmentsTimes) {
    //     $counter = 0;
    //     foreach ($appointmentsTimes as $appointment) {
    //         if ($appointment['status']) {
    //             $counter++;
    //             if ($counter == 2) {
    //                 $time = Carbon::createFromTimeStamp($appointment['time_stamp'], 'Asia/Tehran')->setTimeFromTimeString($appointment['from'])->timestamp;
    //                 $appointmentModel = new AppointmentModel(
    //                     timestamp: $time,
    //                     appointmentVia: AppointmentVia::SELF,
    //                     sendSmsToUser: true,
    //                     serviceId: 1,
    //                     placeId: 1,
    //                     agentId: 1,
    //                     kind: AppointmentUserKindEnum::IN_PERSION,
    //                     smsToDoctor: false,
    //                     description: '',
    //                     type: AppointmentUserTypeEnum::MAIN__APPOINTMENT,
    //                     endTime: Carbon::createFromTimeString($appointment['until'])->toTimeString(),
    //                 );
    //                 $storeAppointment = app('AppointmentUserService')->storeAppointment($setting, $userModelAppointment, $appointmentModel, []);
    //                 $counter = 0;
    //             }
    //         }
    //     }
    // }
});
Route::get('pp', function () {
    $doctor = User::find(5);
    $place = Place::find(1);
    $segments = [1, 2, 3];
    $segmetnss = [];
    foreach ($segments as $item) {
        $segmetnss[] =  AppointmentSegmentItem::find($item);
    }
    if (count($segmetnss) > 1) {
        $seg_time = 0;
        foreach ($segmetnss as $eachSegTime) {
            $seg_time += $eachSegTime->time;
        }
    }
    $appointmentSetting = AppointmentSetting::where('user_id', $doctor->id)->first();
    $details = [];
    if ($seg_time) {
        $details['segment_time'] =  $seg_time;
    }
    $details['specialDays'] = Carbon::parse('2024-10-04');
    $listOfAppointment =  app('AppointmentUserService')->listAppointments($appointmentSetting, $details);
    dd($listOfAppointment['data'][1403][7][28]);
});

Route::get('/notif',function(){
    $user = \Modules\User\Entities\User::find(3);
        $user->notify(new \Modules\User\Notifications\UserMessageNotification(
        title: "test title",
        excerpt: "test excerpt",
        message: 'test message',
        link: \App\Enum\RouteEnum::APPOINTMENT->getLink('2')
    ));
});

Route::get('test_ui', function () {
    dd(disableUi());
});
