<?php

namespace Modules\AppointmentUser\Traits;

use Carbon\Carbon;
use App\Models\ShortLink;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Access\AuthorizationException;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\app\Events\CancelAppointmentEvent;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\app\Notifications\AppointmentUserFeedbackSmsnotification;


//this Trait is return value as a UerMetaEnum not string
trait OprationButtonsTrait
{
    /**
     * The buttons are only hidden in the page, so every action has to check the permission again:
     * a Livewire call can be sent without the button. The appointment is allowed when the user has
     * at least one of the abilities (the operations menu is shown to everyone who can update or delete).
     */
    private function authorizeAppointment($id, string ...$abilities): AppointmentUser
    {
        $app = AppointmentUser::findOrFail($id);
        foreach ($abilities as $ability) {
            if (Gate::allows($ability, $app)) {
                return $app;
            }
        }

        throw new AuthorizationException('شما دسترسی لازم برای این عملیات را ندارید');
    }
    public function changeType($id)
    {
        $app = $this->authorizeAppointment($id, 'update', 'delete');
        $app->update(['type' =>  AppointmentUserTypeEnum::BETWEEN_PATIENTS]);

        // regenerate cache
        $this->reGenerateCacheJob($app);
        $this->sendNotification($app, 'وضعیت نوبت شما به بین مریض تغییر پیدا کرد');
        return  $this->redirectToPage('نوبت به بین مریض تغییر پیدا کرد');
    }
    public function cancelAppointment($id, $sendSmsStatus)
    {
        $app = $this->authorizeAppointment($id, 'update', 'delete');
        $app->update(['status' =>  AppointmentUserStatusEnum::STATUS_CANCEL, 'deadline_at' => null]);
        if ($sendSmsStatus) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_CANCEL);
            if (isset($smsTemplate)) {
                $app->notify(new AppointmentSmsNotification($smsTemplate));
            }
        }
        if ($app->kind == AppointmentUserKindEnum::ONLINE) {
            $app->online()->update([
                'status' => AppointmentOnlineStatusEnum::CANCEL,
            ]);
        }
        // regenerate cache
        $this->reGenerateCacheJob($app);

        $this->sendNotification($app, 'وضعیت نوبت شما به بین مریض تغییر پیدا کرد');
        event(new CancelAppointmentEvent($app));
        return  $this->redirectToPage('نوبت با موفقیت کنسل شد');
    }
    public function cancelSelectedApp()
    {
        $ids = collect($this->form['checkbox'] ?? [])->filter()->keys();
        if ($ids->isEmpty()) {
            return $this->redirectToPage('هیچ نوبتی انتخاب نشده است');
        }
        // check every selected appointment first, so nothing is cancelled when one of them is not allowed
        $appointments = $ids->map(fn ($id) => $this->authorizeAppointment($id, 'update', 'delete'));

        foreach ($appointments as $app) {
            if ($app->status === AppointmentUserStatusEnum::STATUS_CANCEL) {
                continue;
            }
            $app->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL, 'deadline_at' => null]);
            if ($app->kind == AppointmentUserKindEnum::ONLINE) {
                $app->online()->update(['status' => AppointmentOnlineStatusEnum::CANCEL]);
            }
            $this->reGenerateCacheJob($app);
            event(new CancelAppointmentEvent($app));
        }
        $this->form['checkbox'] = [];

        return $this->redirectToPage('نوبت های انتخابی با موفقیت کنسل شدند');
    }
    public function cancelAndDeleteApp($id)
    {
        // deleting is stricter than cancelling: only the delete permission is accepted
        $this->authorizeAppointment($id, 'delete');
        $this->cancelAppointment($id, false);

        $app = AppointmentUser::find($id);
        $app->delete();
        // regenerate cache
        $this->reGenerateCacheJob($app);
        $this->redirectToPage('نوبت با موفقیت حذف شد');
    }
    public function ApprovemonitoringAppointment($id)
    {
        $app = $this->authorizeAppointment($id, 'update', 'delete');
        $deadLine_Time = $app->setting->detail[AppointmentSetting::MONITORTING_APPOINTMENT] ?? 1;
        $Appoointment_dedLine = now()->addHours((int)$deadLine_Time);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT, 'deadline_at' => $Appoointment_dedLine]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT)));
        // regenerate cache
        $this->reGenerateCacheJob($app);
        $this->sendNotification($app, 'نوبت شما تایید شد');
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApprovemonitoringAppointment($id)
    {
        $app = $this->authorizeAppointment($id, 'update', 'delete');
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED, 'deadline_at' => null]);
        // regenerate cache
        $this->reGenerateCacheJob($app);
        $this->redirectToPage('نوبت با موفقیت عدم تایید شد');
    }
    public function disApprovemonitoringAppointmentWithSms($id)
    {
        $app = $this->authorizeAppointment($id, 'update', 'delete');
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED, 'deadline_at' => null]);
        $app->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT)));
        // regenerate cache
        $this->reGenerateCacheJob($app);
        $this->redirectToPage('نوبت با موفقیت عدم تایید شد');
    }
    public function ApproveOnlineAppointment($id)
    {
        $app = $this->authorizeAppointment($id, 'update', 'delete');
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
        // regenerate cache
        $this->reGenerateCacheJob($app);
        $this->sendNotification($app, 'نوبت شما تایید شد');
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApproveOnlineAppointment($id)
    {
        $appointmentUser = $this->authorizeAppointment($id, 'update', 'delete');
        $this->fetchData['disapproveId'] = $id;
        $this->dispatch('lunchModal', true);

        // regenerate cache
        $this->reGenerateCacheJob($appointmentUser);
    }
    public function disaprovedModal()
    {
        $this->validate(['form.reason' => 'required'], ['form.reason.required' =>  'لطفا دلیل رد شدن را بنویسید']);
        $app = $this->authorizeAppointment($this->fetchData['disapproveId'], 'update', 'delete');
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
        // regenerate cache
        $this->reGenerateCacheJob($app);
        $this->redirectToPage('وضعیت نوبت به عدم تایید ، تغییر پیدا کرد');
    }
    public function ignoreDisaproveModal()
    {
        unset($this->fetchData['disapproveId']);
    }

    public function editAppointment($id)
    {
        $app = $this->authorizeAppointment($id, 'update', 'delete');
        $date = verta($app->date_visit)->format('Y-m-d');
        // regenerate cache
        $this->reGenerateCacheJob($app);
        $parameters = [
            'serviceId'     => $app->service_id,
            'placeId'       => $app->place_id,
            'appId'         => $app->setting->id,
            'date'          => $date,
            'tracking_code' => $app->tracking_code
        ];
        if (isset($app->details[AppointmentUser::DETAIL_SEGMENTS])) {
            $segmentsIds =  implode(',', array_column($app->details['segments'], 'id'));
            $parameters['segmentItemId'] = $segmentsIds;
        }
        $this->sendNotification($app, 'ساعت نوبت شما تغییر کرده است');
        return redirect()->route(
            'admin.appointment.add.specificday',
            $parameters
        );
    }
    public function userAttenedToAppointment(AppointmentUser $appointmentUser)
    {
        $this->authorizeAppointment($appointmentUser->id, 'update', 'delete');
        $this->sendfeedBackLink($appointmentUser);
        $this->changeAttendedStatus($appointmentUser, true);
        // regenerate cache
        $this->reGenerateCacheJob($appointmentUser);
        $this->redirectToPage('وضعیت نوبت به کاربر حضور پیدا کرده تغییر کرد');
    }
    public function userNotAttenedToAppointment(AppointmentUser $appointmentUser)
    {
        $this->authorizeAppointment($appointmentUser->id, 'update', 'delete');
        $this->changeAttendedStatus($appointmentUser, false);
        // regenerate cache
        $this->reGenerateCacheJob($appointmentUser);
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
        // regenerate cache
        $this->reGenerateCacheJob($appointmentUser);
        $appointmentUser->update(['details' => $new_details]);
    }
    protected function sendfeedBackLink(AppointmentUser $appointmentUser)
    {
        $link_code = ShortLink::generateShortLinkCode();
        $link_url = $appointmentUser->feedbackUrl();
        ShortLink::create([
            'link_code' => $link_code,
            'link_url'  => $link_url,
            'shortlinkable_type'  => 'feedBack',
            'shortlinkable_id'  => $appointmentUser->id,
        ]);
        $smsTemplate = setting(SettingKeyEnum::SMS_FEEDBACK);
        if (isset($smsTemplate)) {
            $appointmentUser->notify(new AppointmentUserFeedbackSmsnotification(template: $smsTemplate, link_code: $link_code));
        }
        // regenerate cache
        $this->reGenerateCacheJob($appointmentUser);
    }
    private function sendNotification(AppointmentUser $appointmentUser, string $notifMessage)
    {
        if (isset($appointmentUser->details[AppointmentUser::STORE_FROM_APPLICATION]) && $appointmentUser->details[AppointmentUser::STORE_FROM_APPLICATION]) {
            try {
                $appointmentUser->user->notify(new \Modules\User\Notifications\UserMessageNotification(
                    title: "تغییر وضعیت نوبت",
                    excerpt: $notifMessage,
                    message: '',
                    link: \App\Enum\RouteEnum::APPOINTMENT->getLink($appointmentUser->id),
                ));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }
    public function resendPaymentSms(AppointmentUser $appointmentUser)
    {
        $this->authorizeAppointment($appointmentUser->id, 'update', 'delete');
        $appointmentUser->notify(new AppointmentSmsNotification(setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT)));
        $this->redirectToPage('پیامک پرداخت مجدد ارسال شد');
    }
    private function reGenerateCacheJob($app)
    {
        // generate cache
        if(! is_null($app->setting)) {
            $app->setting?->runGenerateCacheJob(specialDayConvert($app->date_visit));
        }
    }
}
