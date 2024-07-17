<?php

namespace Modules\AppointmentUser\Traits;

use App\Models\ShortLink;
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
        event(new CancelAppointmentEvent($app));
        return  $this->redirectToPage('نوبت با موفقیت کنسل شد');
    }
    public function cancelAndDeleteApp($id)
    {
        $this->cancelAppointment($id, true);
        $app = AppointmentUser::find($id);
        $app->delete();
        $this->redirectToPage('نوبت با موفقیت حذف شد');
    }
    public function ApprovemonitoringAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $deadLine_Time = $app->setting->detail[AppointmentSetting::MONITORTING_APPOINTMENT];
        $Appoointment_dedLine = now()->addHours($deadLine_Time);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT, 'deadline_at' => $Appoointment_dedLine]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT)));
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApprovemonitoringAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED]);
        $this->redirectToPage('نوبت با موفقیت عدم تایید شد');
    }
    public function disApprovemonitoringAppointmentWithSms($id)
    {
        $app = AppointmentUser::find($id);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT)));
        $this->redirectToPage('نوبت با موفقیت عدم تایید شد');
    }
    public function ApproveOnlineAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $onlineApp = AppointmentOnline::firstWhere('appointment_user_id', $app->id);
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
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApproveOnlineAppointment($id)
    {
        $this->fetchData['disapproveId'] = $id;
        $this->dispatch('lunchModal', true);
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
            $onlineApp = AppointmentOnline::firstWhere('appointment_user_id', $app->id);
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
        $feddBack = setting(SettingKeyEnum::SMS_FEEDBACK);
        if (isset($feddBack)) {
            $appointmentUser->notify(new AppointmentSmsNotification($feddBack));
        }
        $this->redirectToPage('وضعیت نوبت به کاربر حضور پیدا کرده تغییر کرد');
    }
    public function userNotAttenedToAppointment(AppointmentUser $appointmentUser)
    {
        $this->changeAttendedStatus($appointmentUser, false);
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
        $appointmentUser->update(['details' => $new_details]);
    }
    protected function sendfeedBackLink(AppointmentUser $appointmentUser)
    {
        $link_code = ShortLink::generateShortLinkCode();
        $link_url = route('front.feedBack', ['appointmentUser_id' => $appointmentUser->id]);
        ShortLink::create([
            'link_code' => $link_code,
            'link_url'  => $link_url,
        ]);
        $smsTemplate = setting(SettingKeyEnum::SMS_FEEDBACK);
        if (isset($smsTemplate)) {
            $appointmentUser->notify(new AppointmentUserFeedbackSmsnotification($smsTemplate, $link_code));
        }
    }
}
