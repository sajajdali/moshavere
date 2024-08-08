<?php

namespace Modules\AppointmentUser\Traits;

use App\Models\ShortLink;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\app\Events\CancelAppointmentEvent;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\app\Notifications\AppointmentUserFeedbackSmsnotification;


//this Trait is return value as a UerMetaEnum not string
trait OprationButtonsTrait
{
    public function changeType($id)
    {
        $app = AppointmentUser::find($id);
        $app->update(['type' =>  AppointmentUserTypeEnum::BETWEEN_PATIENTS]);
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->sendNotification($app,'وضعیت نوبت شما به بین مریض تغییر پیدا کرد');
        return  $this->redirectToPage('نوبت به بین مریض تغییر پیدا کرد');
    }
    public function cancelAppointment($id, $sendSmsStatus)
    {
        $app = AppointmentUser::find($id);
        $app->update(['status' =>  AppointmentUserStatusEnum::STATUS_CANCEL]);
        if ($sendSmsStatus) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_CANCEL);
            if (isset($smsTemplate)) {
                $app->notify(new AppointmentSmsNotification($smsTemplate));
            }
        }

        Cache::forget('appointmentList.' . $app->setting->id);
        $this->sendNotification($app,'وضعیت نوبت شما به بین مریض تغییر پیدا کرد');
        event(new CancelAppointmentEvent($app));
        return  $this->redirectToPage('نوبت با موفقیت کنسل شد');
    }
    public function cancelAndDeleteApp($id)
    {
        $this->cancelAppointment($id, true);
        $app = AppointmentUser::find($id);
        $app->delete();
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->redirectToPage('نوبت با موفقیت حذف شد');
    }
    public function ApprovemonitoringAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $deadLine_Time = $app->setting->detail[AppointmentSetting::MONITORTING_APPOINTMENT] ?? 24;
        $Appoointment_dedLine = now()->addHours((int)$deadLine_Time);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT, 'deadline_at' => $Appoointment_dedLine]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT)));
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->sendNotification($app,'نوبت شما تایید شد');
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApprovemonitoringAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED]);
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->redirectToPage('نوبت با موفقیت عدم تایید شد');
    }
    public function disApprovemonitoringAppointmentWithSms($id)
    {
        $app = AppointmentUser::find($id);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT)));
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->redirectToPage('نوبت با موفقیت عدم تایید شد');
    }
    public function ApproveOnlineAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $onlineApp = AppointmentOnline::firstWhere('appointment_user_id', $app->setting->id);
        $detail = [
            AppointmentOnline::COFRIM_OR_REJECT_STATUS => [
                AppointmentOnline::BY => auth()->user()->id,
                AppointmentOnline::DATE => \now(),
            ],
        ];
        if (isset($onlineApp->details)) {
            $detail = array_merge($detail, $onlineApp->details);
        }
        $onlineApp?->update(['status' => AppointmentOnlineStatusEnum::ACCEPTED, 'details' => $detail]);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL]);
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->sendNotification($app,'نوبت شما تایید شد');
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApproveOnlineAppointment($id)
    {
        $appointmentUser = AppointmentUser::find($id);
        $this->fetchData['disapproveId'] = $id;
        $this->dispatch('lunchModal', true);

        Cache::forget('appointmentList.' . $appointmentUser->setting->id);
    }
    public function disaprovedModal()
    {
        $app = AppointmentUser::find($this->fetchData['disapproveId']);
        $reson_for_disapproved = [
            AppointmentUser::DISAPPROVED_DESCRIPTION => $this->form['reason'],
            AppointmentOnline::COFRIM_OR_REJECT_STATUS => [
                AppointmentOnline::BY => auth()->user()->id,
                AppointmentOnline::DATE => \now(),
            ],
        ];
        try {
            $onlineApp = AppointmentOnline::firstWhere('appointment_user_id', $app->setting->id);
            $detail = $onlineApp->details;
            if (isset($detail)) {
                $detail = array_merge($detail, $reson_for_disapproved);
            } else {
                $detail = $reson_for_disapproved;
            }
            $onlineApp->update(['status' => AppointmentOnlineStatusEnum::REJECT, 'details' =>  $detail]);
            $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED]);
        } catch (\Exception $th) {
            return redirect()->route('admin.appointment_user.list')->with('error', 'خطا در به روز رسانی');
        }
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->redirectToPage('وضعیت نوبت به عدم تایید ، تغییر پیدا کرد');
    }
    public function ignoreDisaproveModal()
    {
        unset($this->fetchData['disapproveId']);
    }

    public function editAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $date = verta($app->date_visit)->format('Y-m-d');
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->sendNotification($app,'ساعت نوبت شما تغییر کرده است');
        return redirect()->route(
            'admin.appointment.add.specificday',
            [
                'serviceId'     => $app->service_id,
                'placeId'       => $app->place_id,
                'appId'         => $app->setting->id,
                'date'          => $date,
                'tracking_code' => $app->tracking_code
            ]
        );

    }
    public function userAttenedToAppointment(AppointmentUser $appointmentUser)
    {
        $this->sendfeedBackLink($appointmentUser);
        $this->changeAttendedStatus($appointmentUser, true);
        Cache::forget('appointmentList.' . $appointmentUser->setting->id);
        $this->redirectToPage('وضعیت نوبت به کاربر حضور پیدا کرده تغییر کرد');
    }
    public function userNotAttenedToAppointment(AppointmentUser $appointmentUser)
    {
        $this->changeAttendedStatus($appointmentUser, false);
        Cache::forget('appointmentList.' . $app->setting->id);
        $this->redirectToPage('وضعیت نوبت به کاربر حضور پیدا نکرده تغییر کرد');
    }
    protected function changeAttendedStatus(AppointmentUser $appointmentUser, $status)
    {
        $existin_detial = $appointmentUser->details;
        $user_attended = [AppointmentUser::USRE_ATTENDED_STATUS => $status];
        if (isset($existin_detial)) {
            $new_details =  array_merge($existin_detial, $user_attended);
        } else {
            $new_details = $user_attended;
        }
        Cache::forget('appointmentList.' . $appointmentUser->setting->id);
        $appointmentUser->update(['details' => $new_details]);
    }
    protected function sendfeedBackLink(AppointmentUser $appointmentUser)
    {
        $link_code = ShortLink::generateShortLinkCode();
        $link_url = route('front.feedBack', ['appointmentUser_id' => $appointmentUser->id]);
        ShortLink::create([
            'link_code' => $link_code,
            'link_url'  => $link_url,
            'shortlinkable_type'  => 'feedBack',
            'shortlinkable_id'  => $appointmentUser->id,
        ]);
        $smsTemplate = setting(SettingKeyEnum::SMS_FEEDBACK);
        if (isset($smsTemplate)) {
            $appointmentUser->notify(new AppointmentUserFeedbackSmsnotification($smsTemplate, $link_code));

        }
        Cache::forget('appointmentList.' . $appointmentUser->id);
    }
    // TODO :: refund
    protected function refuntPaiedApp($model)
    {
        // TODO::this isnt working
        $appointmentUser = AppointmentUser::find($model);
        $endpoint = "https://next.zarinpal.com/api/v4/graphql";
        $query = '
            mutation AddRefund($session_id: ID!, $amount: BigInteger!, $description: String, $reason: RefundReasonEnum) {
                resource: AddRefund(session_id: $session_id, amount: $amount, description: $description, reason: $reason) {
                    terminal_id
                    id
                    amount
                    timeline {
                        refund_amount
                        refund_time
                        refund_status
                    }
                }
            }
        ';
        $session_id = $appointmentUser->transaction->detail['transactionId'];
        $amount     = $appointmentUser->transaction->total_cost;
        $description = 'بازگشت وجه نوبت' . $appointmentUser->id;
        $reason     = 'بازگشت وجه نوبت' . $appointmentUser->id;

        $merchenId  = setting(SettingKeyEnum::PAYMENT_ZARINPAL_MERCHENID);
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $merchenId,
        ])->post('https://next.zarinpal.com/api/v4/graphql/', [
            'query' => $query,
            'operationName' => 'AddRefund',
            'variables' => [
                'session_id' => $session_id,
                'amount' => $amount,
                'description' => $description,
                'reason' => $reason,
            ],
        ]);

        if ($response->successful()) {
            $arr_response = $response->json();
            if (!empty($arr_response) && isset($arr_response['data']['resource']['amount'])) {
                $this->cancelAppointment($appointmentUser->id, false);
                $old_details = $appointmentUser->details;
                $new_details = array_merge($old_details, ['refund' => $arr_response]);
                $appointmentUser->update(['details' => $new_details]);
                $smsTemplate = setting(\Modules\Setting\Enum\SettingKeyEnum::SMS_AFTER_REFUND);
                if ($smsTemplate) {
                    $appointmentUser->notify(new AppointmentSmsNotification($smsTemplate));
                }
                Cache::forget('appointmentList.' . $appointmentUser->id);
                $this->redirectToPage('وضعیت نوبت به کاربر حضور پیدا نکرده تغییر کرد');
            }
            $this->sendNotification($appointmentUser,'وجه پرداختی به جساب شما بازگشت داده شد');
        } else {
            $this->redirectToPage('خطا');
        }
    }

    private function sendNotification(AppointmentUser $appointmentUser, string $notifMessage)
    {
        if (isset($appointmentUser->details[AppointmentUser::STORE_FROM_APPLICATION]) && $appointmentUser->details[AppointmentUser::STORE_FROM_APPLICATION]) {
            $appointmentUser->user->notify(new \Modules\User\Notifications\UserMessageNotification(
                title: "تغییر وضعیت نوبت",
                excerpt: $notifMessage,
                message: '',
                link: \App\Enum\RouteEnum::APPOINTMENT->getLink($appointmentUser->id) ,
            ));
        }
    }
}
