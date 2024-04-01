<?php

namespace Modules\Api\Http\Controllers\Voip;

use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
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
                if ($month < $isMonth) {
                    continue;
                }
                foreach ($appointments as $day => $appointment) {
                    if ($day < $isDay || $appointment['empty_appoints'] <= 0 || $appointment['status'] == false) {
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
                                $result['day' . $dayCount]['times'][] = ['time_stamp' => $time['timestamp']];
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
        $appointmentSetting = AppointmentSetting::where('user_id', $doctorId);

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
        Cache::forget('appointmentList.' . $appointmentSetting->id);
        $listDays = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });
        return $this->ok(
            $this->getListEmptyAppointment($listDays)
        );
    }
}
