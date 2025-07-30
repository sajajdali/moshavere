<?php

namespace Modules\Api\Http\Controllers\Voip;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;
use Modules\User\Entities\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\Api\Http\Controllers\Appointment\AppointmentApiController;

class VoipController extends Controller
{
    use ApiHandlerTrait;

    private function getListEmptyAppointment($data)
    {
        $report = $data['report'];
        $mainDaActive = $report['min_day_active'];

        $isDay = verta()->addDays($mainDaActive)->day;
        $isMonth = verta()->addDays($mainDaActive)->month;
        $isYear = verta()->addDays($mainDaActive)->year;

        $result = [];
        $maxDay = 15;
        $DaysDisplayed = 0;
        $dayCount = 0;
        foreach ($data['data'] as $yeay => $day) {
            if ($yeay < $isYear) {
                continue;
            }
            foreach ($day as $month => $appointments) {

                foreach ($appointments as $day => $appointment) {

                    if ($day < $isDay && $month < $isMonth && $yeay < $isYear) {
                        continue;
                    }
                    if ($appointment['empty_appoints'] <= 0 || $appointment['status'] == false) {
                        continue;
                    }
                    $dayNumber = $appointment['day_number'];
                    $DaysDisplayed++;

                    if ($DaysDisplayed > 15) {
                        break 3;
                    }
                    $dayCount++;

                    if (count($appointment['times'])) {
                        foreach ($appointment['times'] as $time) {
                            if ($time['status']) {
                                $result['day' . $dayCount]['times'][] = ['timestamp' => $time['timestamp']];
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function listDays(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $placesId = $request->get('places_id');
        $servicesId = $request->get('services_id');

        $appointmentApiController = new AppointmentApiController();
        $findAlterNateDoctor = $appointmentApiController->findAlterNateDoctor();


        if ($findAlterNateDoctor == null) {
            return null;
        }
        $appointmentSetting = AppointmentSetting::where('user_id', $findAlterNateDoctor->id);

        if ($placesId) {
            $appointmentSetting->where('place_id', $placesId);
        } else {
            $appointmentSetting->whereNull('place_id');
        }
        if ($servicesId) {
            $appointmentSetting->where('service_id', $servicesId);
        } else {
            $appointmentSetting->whereNull('service_id');
        }
        $appointmentSetting = $appointmentSetting->first();

        if (!$appointmentSetting) {
            return $this->requestException([
                'status' => false,
                'message' => 'هیچ اطلاعاتی یاف تشد'
            ]);
        }


        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $appointmentSetting->id);
        }
        $listDays = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            $appointmentSetting->update(['updated_log_at' => \now()]);
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });
        return $this->ok(
            [
                'doctor_selected' => $findAlterNateDoctor->id,
                'appointment_setting_id' => $appointmentSetting->id,
                'empty_times' => $this->getListEmptyAppointment($listDays),
            ]
        );
    }

    public function storeAppointment(Request $request)
    {
        $doctorId            = $request->get('doctor_id');
        $visitDate           = $request->get('visit_date');
        $mobile              = $request->get('user_mobile');
        $placesId            = $request->get('place_id');
        $servicesId          = $request->get('service_id');
        $operatorId          = $request->get('operator_id');
        $kindParameter       = $request->get('kind');
        $description         = $request->get('description');
        if (empty($doctorId) || empty($visitDate) || empty($mobile)) {
            return $this->requestException([
                'status' => false,
                'message' => ' پزشک و تاریخ نوبت , شماره همراه کاربر الزامی است.'
            ]);
        }

        $doctor = User::find($doctorId);
        $startDate = Carbon::createFromTimestamp($visitDate, 'Asia/Tehran');
        if ($startDate->isPast()) {
            return $this->requestException([
                'status' => false,
                'message' => 'تاریخ انتخابی صحیح نیست.'
            ]);
        }

        $appointmentSetting = $doctor->appointmentSettings()->active()
            ->when(isset($servicesId), function ($q) use ($servicesId) {
                return $q->where('service_id', $servicesId)->orWhere('service_id', null);
            })->when(!isset($servicesId), function ($q) {
                return $q->whereNull('service_id')->orWhereNull('service_id');
            })->when(isset($placesId), function ($q) use ($placesId) {
                return $q->where('place_id', $placesId)->orWhereNull('place_id');
            })->when(! isset($placesId), function ($q) {
                return $q->whereNull('place_id');
            })->when(isset($operatorId), function ($q) use ($operatorId) {
                return $q->whereNotNull('detail->ids')->whereJsonContains('detail->ids', $operatorId);
            })->first();


        if (! isset($appointmentSetting)) {
            return $this->requestException([
                'status' => false,
                'message' => 'تنظیمات مربوط به پزشک پیدا نشد ، بخش،مطب،یا اپراتور را بررسی کنید.'
            ]);
        }
        $endDate = $startDate->addMinutes($appointmentSetting->time_for_visit);
        $user = User::where('mobile', 'LIKE', "%{$mobile}%")->first();
        if (!isset($user)) {
            $user  = User::create([
                'mobile' => $mobile,
                'password' => uniqId(),
            ]);
        }

        $someoneModel = null;
        $foHimself = 1;
        // main user data
        $mainUser = new UserModel(
            user: $user,
            firstName: $user->first_name,
            lastName: $user->last_name,
        );
        // full user model
        $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel);

        //check if operator
        if (isset($operatorId) && !empty($operatorId)) {
            $oprator = null;
            $operatorExists = User::find($operatorId);
            if ($operatorExists) {
                // check if oprator exixts
                $oprator =  $operatorId;
            }
        } else {
            $oprator = null;
        }
        if (isset($kindParameter) && ! empty($kindParameter)) {
            $kind = AppointmentUserKindEnum::tryFrom($kindParameter);
        } else {
            $kind = AppointmentUserKindEnum::IN_PERSION;
        }
        // appointment model
        $appointmentModel = new AppointmentModel(
            timestamp: $startDate->copy()->timestamp,
            appointmentVia: AppointmentVia::SELF,
            sendSmsToUser: true,
            serviceId: $servicesId ?? $doctor->service->first(),
            placeId: $placesId ?? $doctor->place->first(),
            agentId: $user->id,
            kind: $kind,
            smsToDoctor: false,
            description: $description ??  '',
            type: AppointmentUserTypeEnum::MAIN__APPOINTMENT,
            endTime: $endDate->toTimeString(),
        );

        $detail = [];

        // sms Template
        if (
            $appointmentSetting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::STATUS] == true &&
            $appointmentSetting->detail[AppointmentSetting::PAYMENT][AppointmentSetting::NOT_PAYING_STATUS] == 'dontSubmit'
        ) {
            // if payment was active
            $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
        } else {
            $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);
        }

        $storeAppointment = app('AppointmentUserService')->storeAppointment($appointmentSetting, $userModelAppointment, $appointmentModel, $detail);
        if ($storeAppointment['status']) {
            // generate cache
            $appointmentSetting->runGenerateCacheJob($startDate->toDateTimeString());
            return $this->ok(
                [
                    'status' => true,
                    'message' => 'نوبت با موفقیت ذخیره شد'
                ]
            );
        } else {
            return $this->requestException([
                'status' => false,
                'message' => 'خطا در ثبت نوبت.'
            ]);
        }
    }
}
