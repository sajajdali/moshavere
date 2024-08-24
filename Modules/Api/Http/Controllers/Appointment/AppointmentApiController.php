<?php

namespace Modules\Api\Http\Controllers\Appointment;

use Verta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\User;
use Illuminate\Routing\Controller;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Service\app\Models\Service;
use Modules\Api\Enum\ServiceQuestionEnum;
use Modules\Api\Transformers\UserResource;
use Modules\Api\Enum\UserVisitedStatusEnum;
use Modules\Api\app\Resources\Api\PlaceResource;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\Api\app\Resources\Api\ServiceResource;
use Modules\AppointmentUser\Enum\model\BirthdayModel;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\Api\app\Resources\Api\Appointments\DoctorResource;
use Modules\Api\app\Resources\Api\Appointments\AppointmentUserResource;
use Modules\Api\app\Http\Requests\Api\Requests\Appointment\StoreAppointmentUserRequest;

class AppointmentApiController extends Controller
{
    use ApiHandlerTrait;

    public function userInfo(): \Illuminate\Http\JsonResponse
    {

        $user = auth()->user();
        return $this->ok([
            'status' => true,
            'user' => UserResource::make($user),
        ]);
    }
    public function doctorsList(Request $request)
    {
        $query = User::doctors_query();

        if ($request->has('type') && in_array($request->get('type'), [1, 2])) {

            $query->whereHas('appointmentSettings', function ($query) use ($request) {
                if ($request->get('type') == 1) {
                    $query->where('detail->visit_type_inPerson', true);
                } elseif ($request->get('type') == 2) {
                    $query->where('detail->visit_type_online', true);
                }
            });
        }

        $doctors = $query->get();

        return $this->ok([
            'status' => true,
            'doctors' => DoctorResource::collection($doctors)
        ]);
    }

    public function doctorProfile(User $doctor)
    {
        return $this->ok([
            'status' => true,
            'profile' => DoctorResource::make($doctor)
        ]);
    }

    public function places(User $doctor)
    {
        $places = $doctor->places()->Active()->orderBy('priority')->get();
        return $this->ok(
            [
                'status' => true,
                'places' => PlaceResource::collection($places)
            ]
        );
    }

    public function services(User $doctor)
    {
        $services = ServiceResource::collection($doctor->services()->whereNull('parent_id')->Active()->orderBy('priority')->get());

        return $this->ok(
            [
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
            nationalCode: $request->input('national_code'),
            address: $request->input('address'),
            city: $request->input('city'),
            birthday: new BirthdayModel(day: $request->input('birthday_day'), month: $request->input('birthday_month'), year: $request->input('birthday_year'))
        );

        // full user model
        $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel, needToUpdate: true);

        $kind = $request->input('kind') == 2 ? AppointmentUserKindEnum::ONLINE : AppointmentUserKindEnum::IN_PERSION;
        $serviceId = $request->input('service_id');

        // Pragnency subservice selection
        if ($serviceId == 1 && $request->has('question')) {
            $subServiceTitle =   ServiceQuestionEnum::tryFrom($request->input('question'));
            $service = Service::where('title', 'LIKE', $subServiceTitle->getName())?->first();
            if (isset($service)) {
                $serviceId = $service->id;
            }
        }
        // zibaii subservice selection
        if ($serviceId == 3 && $request->has('question')) {
            $serviceId = $request->input('question');
        }
        // appointment model
        $appointmentModel = new AppointmentModel(
            timestamp: $request->input('timestamp') ?? null,
            appointmentVia: AppointmentVia::SELF,
            sendSmsToUser: true,
            serviceId: $serviceId,
            placeId: $request->input('place_id') ?? $appointmentSetting->user->activePlaces()->first()?->id,
            kind: $kind
        );

        $detail = [];
        if ($request->input('question')) {
            $detail[AppointmentUser::DETAIL_QUESTION] = $request->input('question');
        }
        $detail[AppointmentUser::STORE_FROM_APPLICATION] = true;
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
        $doctorId   = $request->get('doctor_id');
        $placesId   = $request->get('places_id');
        $servicesId = $request->get('services_id');
        $kind       = $request->get('kind') ?? 1; // in person or online
        $question  = $request->input('question');
        $hasVisited = \Modules\Api\Enum\UserVisitedStatusEnum::tryFrom($request->input('has_visited'));

        $conditions = $alert = null;

        $appointmentSetting = AppointmentSetting::where('user_id', $doctorId);

        // online
        if ($kind == AppointmentUserKindEnum::ONLINE->value) {
            $appointmentSetting->where('detail->visit_type_online', true);
        } // in person
        else {
            $appointmentSetting->where('detail->visit_type_inPerson', true);
        }

        if ($placesId) {
            if (AppointmentSetting::where('user_id', $doctorId)->where('place_id', $servicesId)->count()) {
                $appointmentSetting->where('place_id', $placesId);
            } else {
                $appointmentSetting->whereNull('place_id');
            }
        }

        if ($servicesId) {
            if (AppointmentSetting::where('user_id', $doctorId)->where('service_id', $servicesId)->count()) {
                $appointmentSetting->where('service_id', $servicesId);
            } else {
                $appointmentSetting->whereNull('service_id');
            }
        }
        if ($servicesId ==  3) {
            $servicesId =  $question;
        }

        $appointmentSetting = $appointmentSetting->first();

        if (!$appointmentSetting) {
            return $this->requestException([
                'status' => false,
                'message' => 'هیچ اطلاعاتی یاف تشد'
            ]);
        }

        if ($kind == AppointmentUserKindEnum::ONLINE->value) {
            $payment = app('AppointmentUserService')->paymentstatus($appointmentSetting) ;
            return $this->ok([
                'status' => true,
                'payment' => $payment['online'],
                'appointment_setting_id' => $appointmentSetting->id,
                'messages' => [
                    'پس از ثبت درخواست امکان آپلود مدارک و طرح سوال فعال میگردد',
                    'نوبت شما پس از تایید پزشک فعال میشود و در صورت عدم تایید وجه پرداختی عودت داده میشود'
                ],
                'first_two_empty' => null,
                'get_list_empty_appointment' => null,
            ]);
        }

        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $appointmentSetting->id);
        }
        $listDays = Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
            $appointmentSetting->update(['updated_log_at' => \now()]);
            return app('AppointmentUserService')->listAppointments($appointmentSetting);
        });


        //        $firstTwoEmpty = $this->getFirstTwoEmpty($listDays);
        $resultList = $this->getListEmptyAppointment($listDays);

        // handle condition dr amiri
        if ($doctorId == 2) {
            // bardari
            if ($servicesId == 1) {
                if ($hasVisited == UserVisitedStatusEnum::DOSET_VISITED) {
                    $alert['title'] = 'بسیار مهم';
                    $alert['message'] = 'اولین ویزیت شما در هر هفته از بارداری، توسط دکتر امیری انجام میگردد';
                    $alert['alternative_doctor'] = null;
                    $alert['button_text'] = 'تایید میکنم';
                } else {
                    if ($question == 2) {
                        $conditions['title'] = 'امکان دریافت نوبت با دکتر امیری فراهم نیست';
                        $conditions['message'] = 'مراجعه کنندگان گرامی ویزیت بارداران فقط تا ۱۲ هفته توسط دکتر امیری انجام میشود . و بعد از آن توسط تیم فوق تخصصی دکتر امیری (دکتر سهامیررضا) انجام میشود.
ویزیت آخر قبل از سزارین  با دکتر امیری انجام میشود. ';
                        $conditions['alternative_doctor'] = DoctorResource::make(User::doctors_query()->whereHas('metas', function ($q) {
                            $q->where([
                                ['meta_key', UserMetaEnum::FIRST_NAME],
                                ['meta_value', 'LIKE', "%سها%"]
                            ])->orWhere(function ($query) {
                                $query->where([
                                    ['meta_key', UserMetaEnum::LAST_NAME],
                                    ['meta_value', 'LIKE', "%میررضا%"]
                                ]);
                            });
                        })->first());
                        $conditions['button_text'] = 'انتخاب پزشک دیگر';
                    }
                }
            } elseif ($servicesId == 2 || $servicesId == 4) {
                if ($hasVisited == UserVisitedStatusEnum::DOSET_VISITED) {
                    $conditions['title'] = 'امکان دریافت نوبت با دکتر امیری فراهم نیست';
                    $conditions['message'] = 'مراجعه کننده گرامی  ویزیت اولیه شما توسط تیم فوق تخصصی دکتر امیری انجام میشود.
بررسی های اولیه و آزمایشات لازم زیر نظر دکتر امیری نوشته میشود و شما برای ویزیت های بعدی میتوانید با دکتر امیری نوبت دریافت کنید.';
                    $conditions['alternative_doctor'] = DoctorResource::make(User::doctors_query()->whereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::FIRST_NAME],
                            ['meta_value', 'LIKE', "%سها%"]
                        ])->orWhere(function ($query) {
                            $query->where([
                                ['meta_key', UserMetaEnum::LAST_NAME],
                                ['meta_value', 'LIKE', "%میررضا%"]
                            ]);
                        });
                    })->first());
                    $conditions['button_text'] = 'انتخاب پزشک دیگر';
                } else {
                    $alert['title'] = 'شما تایید میکنید که قبلا از دکتر امیری نوبت دریافت کرده اید';
                    $alert['message'] = 'در صورتی که سابقه ویزیت با دکتر امیری نداشته باشید، نوبت شما حذف میشود .';
                    $alert['alternative_doctor'] = null;
                    $alert['button_text'] = 'تایید میکنم';
                }
            }
        }
        // handle condition dr amiri

        $payment = app('AppointmentUserService')->paymentstatus($appointmentSetting) ;
        return $this->ok([
            'status' => true,
            'payment' => !$payment['in_person']['status'] ? null : $payment['in_person'],
            'appointment_setting_id' => $appointmentSetting->id,
            'first_two_empty' => $resultList['firstTwoEmpty'],
            'get_list_empty_appointment' => $resultList['listAppointments'],
            'conditions' => $conditions,
            'alert' => $alert,
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
