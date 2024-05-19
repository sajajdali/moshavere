<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortLinkController;
use Modules\Reminder\Enum\ReminderStatusEnum;
use Modules\Reminder\Enum\ReminderParametersEnum;
use Modules\Reminder\app\Models\AppointmentReminder;
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
