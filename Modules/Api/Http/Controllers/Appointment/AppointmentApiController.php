<?php

namespace Modules\Api\Http\Controllers\Appointment;

use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Api\app\Http\Requests\Api\Requests\Appointment\StoreAppointmentUserRequest;
use Modules\Api\app\Resources\Api\Appointments\AppointmentUserResource;
use Modules\Api\app\Resources\Api\Appointments\DoctorResource;
use Modules\Api\app\Resources\Api\PlaceResource;
use Modules\Api\app\Resources\Api\ServiceResource;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\model\BirthdayModel;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
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
                            $firstTwoEmpty[$matchesFound]['status'] = true;
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

    private function getListEmptyAppointment($data)
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
                            $vertaDateTime = Verta::createTimestamp($time['timestamp']);
                            // Increment the counter

                            if (count($firstTwoEmpty) < 2) {

                                $firstTwoEmpty[] = [
                                    'status' => true,
                                    'persian_date' => $vertaDateTime->format('ساعت H روز l m/d'),
                                    'time_stamp' => $time['timestamp'],
                                    'from' => substr($time['from'], 0, -3),
                                    'until' => substr($time['until'], 0, -3),
                                ];
                            } else {
                                $result[$dayNumber][] = [
                                    'status' => true,
                                    'persian_date' => $vertaDateTime->format('ساعت H روز l m/d'),
                                    'time_stamp' => $time['timestamp'],
                                    'from' => substr($time['from'], 0, -3),
                                    'until' => substr($time['until'], 0, -3),
                                ];
                            }
                            // If two matches are found, break out of the loop
                        } else {
                            $result[$dayNumber][] = [
                                'status' => false,
                                'from' => substr($time['from'], 0, -3),
                                'until' => substr($time['until'], 0, -3),
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

    public function store(StoreAppointmentUserRequest $request)
    {
        $user = auth()->user();
        $appointmentSetting = AppointmentSetting::findOrFail($request->input('appointment_setting_id'));

        // If he wants to take the turn for someone else
        $foHimself = $request->input('form_himself');
        $someoneModel = null;
        if ($foHimself == 2) {
            $someoneModel = new UserModel(
                firstName: $request->input('someone_first_name'),
                lastName: $request->input('someone_last_name'),
                mobile: $request->input('someone_mobile'),
                gender: $request->input('someone_gender'),
                nationalCode: $request->input('someone_national_code')
            );
        }

        // main user data
        $mainUser = new UserModel(
            user: $user,
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            gender: $request->input('gender'),
            birthday: new BirthdayModel(day: $request->input('birthday_day'), month: $request->input('birthday_month'), year: $request->input('birthday_year')),
            nationalCode: $request->input('national_code'),
            address: $request->input('address'),
            city: $request->input('city')
        );

        // full user model
        $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel);

        $kind = $request->input('kind') == 2 ? AppointmentUserKindEnum::ONLINE : AppointmentUserKindEnum::IN_PERSION;
        // appointment model
        $appointmentModel = new AppointmentModel(
            timestamp: $request->input('timestamp'),
            appointmentVia: AppointmentVia::SELF,
            sendSmsToUser: true,
            serviceId: $request->input('service_id'),
            placeId: $request->input('place_id'),
            kind: $kind
        );

        $detail = [];
        if ($request->input('question')) {
            $detail[AppointmentUser::DETAIL_QUESTION] = $request->input('question');
        }
        $storeAppointment = app('AppointmentUserService')->storeAppointment($appointmentSetting, $userModelAppointment, $appointmentModel, $detail);
        if (!$storeAppointment['status']) {
            return $this->requestException([
                'status' => false,
                'message' => $storeAppointment['message'],
                'route' => $storeAppointment['route'] ?? null
            ]);
        }
        return $this->created($storeAppointment);
    }

    public function listDays(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $placesId = $request->get('places_id');
        $servicesId = $request->get('services_id');
        $kind = $request->get('kind') ?? 1; // in person or online

        $appointmentSetting = AppointmentSetting::where('user_id', $doctorId);

        // online
        if ($kind == AppointmentUserKindEnum::ONLINE->value) {
            $appointmentSetting->where('detail->visit_type_online', true);
        } // in person
        else {
            $appointmentSetting->where('detail->visit_type_inPerson', true);
        }

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

        if ($kind == AppointmentUserKindEnum::ONLINE->value) {
            return $this->ok([
                'status' => true,
                'payment' => app('AppointmentUserService')->paymentstatus($appointmentSetting),
                'appointment_setting_id' => $appointmentSetting->id,
                'messages' => [
                    'پس از ثبت درخواست امکان آپلود مدارک و طرح سوال فعال میگردد',
                    'اگر باردار هستید و اولین بار هست که به ما مراجعه میکنید لطفا فرم بارداری رو تکمیل بفرمایید.اگر میخواهید اقدام به بارداری کنید لطفا فرم ویزیت را تکمیل بفرمایید.',
                    'نوبت شما پس از تایید پزشک فعال میشود و در صورت عدم تایید وجه پرداختی عودت داده میشود'
                ],
                'first_two_empty' => null,
                'get_list_empty_appointment' => null,
            ]);
        }


        Cache::forget('appointmentList.' . $appointmentSetting->id);
        $listDays = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });


//        $firstTwoEmpty = $this->getFirstTwoEmpty($listDays);
        $resultList = $this->getListEmptyAppointment($listDays);

        return $this->ok([
            'status' => true,
            'payment' => app('AppointmentUserService')->paymentstatus($appointmentSetting),
            'appointment_setting_id' => $appointmentSetting->id,
            'first_two_empty' => $resultList['firstTwoEmpty'],
            'get_list_empty_appointment' => $resultList['listAppointments'],
            'messages' => null
        ]);
    }

    public function tracking(AppointmentUser $appointmentUser)
    {
        $user = auth()->user();
        if ($appointmentUser->user->id !== $user->id) {
            return $this->requestException([
                'status' => false,
                'message' => 'این نوبت متعلق به شما نیست'
            ]);
        }
        return $this->ok([
            'status' => true,
            'appointment_user' => AppointmentUserResource::make($appointmentUser),
        ]);
    }

}
