<?php

namespace Modules\Api\Http\Controllers\Voip;

use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\Api\Http\Controllers\Appointment\AppointmentApiController;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;

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


        if ($findAlterNateDoctor == null){
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
}
