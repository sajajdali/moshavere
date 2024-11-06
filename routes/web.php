<?php

use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Shetabit\Payment\Facade\Payment;
use Illuminate\Support\Facades\Route;
use Modules\Setting\Enum\SettingKeyEnum;
use App\Http\Controllers\ShortLinkController;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;

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

Route::get('/s/{param}', [ShortLinkController::class, 'index']);

// Route::get('teettt', function () {
//     $appointmentUser = AppointmentUser::find(12576);
//     $amount = $appointmentUser->details[AppointmentUser::DETAIL_PAYMENT][AppointmentUser::DETAIL_PAYMENT_PRICE]['int'];


//     $receipt = \Shetabit\Payment\Facade\Payment::amount($amount)
//         ->transactionId($appointmentUser->transaction->detail['transactionId'])
//         ->verify();
//     dd($receipt);
//     if ($receipt) {
//         $appointmentUser->update([
//             'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
//             'deadline_at' => null
//         ]);
//         // if appointment is online
//         if ($appointmentUser->kind == AppointmentUserKindEnum::ONLINE) {
//             $appointmentUser->online->first()->update(['status' => \Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum::ACCEPTED]);
//             // send online first message
//             if (setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_STATUS)) {
//                 $appointmentUser->online->last()->messages()->create([
//                     'user_id' => $appointmentUser->online->last()->user_id,
//                     'answer_by' => 1,
//                     'type' => AppointmentOnlineMessageTypeEnum::ANSWER,
//                     'seen' => AppointmentOnlineMessageSeenEnum::UNSEEN,
//                     'body' => setting(SettingKeyEnum::ONILNE_SEND_ATUOMATIC_MESSAGE_MESSAGE) ?? 'سلام لطفا سوال خود را مطرح کنید',
//                 ]);
//             }
//         }
//         $tDetail =  $appointmentUser->transaction->detail;
//         $respondDetaul = $receipt->getDetails();
//         $newTdetail = array_merge($tDetail, [
//             'card_hash' => $respondDetaul['card_hash'],
//             'ref_id' => $respondDetaul['ref_id'],
//         ]);
//         $appointmentUser->transaction->update(['status' => TransactionStatusEnum::SUCCESSFUL, 'detail' => $newTdetail]);
//     }
// });
