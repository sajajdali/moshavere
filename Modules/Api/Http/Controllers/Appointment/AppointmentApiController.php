<?php

namespace Modules\Api\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Api\app\Resources\Api\Appointments\DoctorResource;
use Modules\Api\app\Resources\Api\PlaceResource;
use Modules\Api\app\Resources\Api\ServiceResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\User\Entities\User;
use Verta;

class AppointmentApiController extends Controller
{
    use ApiHandlerTrait;

    public function doctorsList()
    {
        $user = auth()->user();
        $doctors = User::doctors_query()->whereHas('appointmentSettings')->get();

        return $this->ok([
                'status' => true,
                'name' => $user->id,
                'doctors' => DoctorResource::collection($doctors)
            ]
        );
    }

    public function places(User $doctor)
    {
        $places = $doctor->places()->Active()->orderBy('priority')->get();
        return $this->ok([
                'status' => true,
                'places' => PlaceResource::collection($places)
            ]
        );
    }

    public function services(User $doctor)
    {
        $services = ServiceResource::collection($doctor->services()->Active()->orderBy('priority')->get());

        return $this->ok([
                'status' => true,
                'services' => $services
            ]
        );
    }

    private function getFirstTwoEmpty($data)
    {
        $matchesFound = 0;
        $firstTwoEmpty = [];

        foreach ($data['data'] as $day) {
            foreach ($day as $appointments) {
                foreach ($appointments as $appointment) {
                    foreach ($appointment['times'] as $time) {
                        if (isset($time['timestamp']) && $time['timestamp'] > Carbon::now()->addHours(4)->timestamp) {
                            // Store the matching timestamp
                            $vertaDateTime = Verta::createTimestamp($time['timestamp']);

                            $firstTwoEmpty[$matchesFound]['time_stamp'] = $time['timestamp'];
                            $firstTwoEmpty[$matchesFound]['from'] = $time['from'];
                            $firstTwoEmpty[$matchesFound]['until'] = $time['until'];
                            $firstTwoEmpty[$matchesFound]['persian_date'] = $vertaDateTime->format('ساعت H روز l m/d');

                            // Increment the counter
                            $matchesFound++;

                            // If two matches are found, break out of the loop
                            if ($matchesFound == 2) {
                                break 4;
                            }
                        }
                    }
                }
            }
        }
        return $firstTwoEmpty;
    }

    private function getListEmptyAppointment( $data)
    {
        $firstTwoEmpty = [];
        $report = $data['report'];
        $mainDaActive = $report['min_day_active'];

        $isDay = verta()->addDays($mainDaActive)->day;
        $isMonth = verta()->addDays($mainDaActive)->month;
        $isYear = verta()->addDays($mainDaActive)->year;

        $result = [];
        $maxDay = 15;
        $DaysDisplayed = 0;

        $firstTwoEmpty = [];
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

                    foreach ($appointment['times'] as $time) {


                        if ($time['status']) {
                            // Increment the counter
                            $result[$dayNumber][] = [
                                'status' => true,
                                'time_stamp' => $time['timestamp'],
                                'from' => str_replace(':00', '', $time['from']),
                                'until' => str_replace(':00', '', $time['until']),
                            ];
                            if (count($firstTwoEmpty) < 2) {
                                $vertaDateTime = Verta::createTimestamp($time['timestamp']);
                                $firstTwoEmpty[] = [
                                    'persian_date' => $vertaDateTime->format('ساعت H روز l m/d'),
                                    'time_stamp' => $time['timestamp'],
                                    'from' => str_replace(':00', '', $time['from']),
                                    'until' => str_replace(':00', '', $time['until']),
                                ];
                            }
                            // If two matches are found, break out of the loop
                        } else {
                            $result[$dayNumber][] = [
                                'status' => false,
                                'from' => str_replace(':00', '', $time['from']),
                                'until' => str_replace(':00', '', $time['until']),
                            ];
                        }
                    }
                }
            }
        }
        return [
            'firstTwoEmpty' => $firstTwoEmpty,
            'listAppointments' => $result
        ];
    }

    public function store(Request $request)
    {
        $appointmentSetting = AppointmentSetting::findOrFail($request->input('appointment_setting_id'));
        $appointmentUser = [
            'timestamp' => $request->input('timestamp'),
        ];
        $timestamp          = $request->input('timestamp');
        app('AppointmentUserService')->storeAppointment($appointmentSetting , [] , $appointmentUser);
    }
    public function listDays(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $placesId = $request->input('places_id');
        $servicesId = $request->input('services_id');

        $appointmentSetting = AppointmentSetting::where('user_id', $doctorId);
        if ($placesId) {
            $appointmentSetting->where('place_id', $placesId);
        }
        if ($servicesId) {
            $appointmentSetting->where('service_id', $servicesId);
        }
        $appointmentSetting = $appointmentSetting->first();

        if (!$appointmentSetting) {
            return $this->requestException([
                'status' => false,
                'message' => 'هیچ اطلاعاتی یاف تشد'
            ]);
        }


//        Cache::forget('appointmentList.'.$appointmentSetting->id);
        $listDays = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });


//        $firstTwoEmpty = $this->getFirstTwoEmpty($listDays);
        $resultList = $this->getListEmptyAppointment( $listDays);

        return $this->ok([
            'status' => true,
            'appointment_setting_id' => $appointmentSetting->id,
            'firstTwoEmpty' => $resultList['firstTwoEmpty'],
            'getListEmptyAppointment' => $resultList['listAppointments']
        ]);
    }

}
