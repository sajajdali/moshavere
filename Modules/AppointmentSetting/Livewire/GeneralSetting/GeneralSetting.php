<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Illuminate\Validation\Rule;
use Modules\User\Entities\User;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\AppointmentUser\app\Jobs\CacheJob;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingInterface;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingPaymentStatus;

class GeneralSetting extends Component
{
    public ?User $user;
    public array $fetchData = [];
    public $isEdited = false;
    public $isSpecialTimeEdited = false;
    public ?AppointmentSetting $appointment_setting;
    /*
    minDayAvaialbe
    maxDayAvaialbe
    maxAvailabeAppointment
    */

    public array $form = [
        'minDayAvaialbe' => 0,
        'maxDayAvaialbe' => 20,
        'avtive' => true,
        // special day setting proprty
        'specialTimeCounter' => [0 => 1],
        'specialDaySetting' => 1,
        'timeitrator' => [0 => 0],
        // special day setting proprty
        //specaial day timeperiod values
        'specialDaydateValues',
        'specialDaytimeValues',
        'monitoring',
        'accessibility' => [
            'dont_show_times' => [
                'status' => false,
                'message' => null
            ],
            'disable_online' => [
                'status' => false,
                'message' => null
            ],
            'online' => [
                'can_send_voice' => true
            ]
        ],
        'operators' => [],
    ];

    /*
    description of form property
     *specialDaySetting          : proprty is used in special day setting and its itrator for how many day has special setiing
     *specialTimeCounter         : proprty is used in special day settingو and its itrator for how many day has special time period for each dateTime
     *timeitrator                : is the itrator for time period in each section of special day
     *specialDaydateValues       : store the date of each special day (array) key: day iterator value: date in shamsi
    */

    //day property
    public array $timeFrame = [];
    public array $counter = [
        'saturday'   => 1,
        'sunday'     => 1,
        'monday'     => 1,
        'tuesday'    => 1,
        'wednesday'  => 1,
        'thursday'   => 1,
        'friday'     => 1,
    ];

    public function updated($property, $value)
    {
        $dayType = '';
        match ($property) {
            'form.visitType.saturday'  => $dayType = 'saturday',
            'form.visitType.sunday'    => $dayType = 'sunday',
            'form.visitType.monday'    => $dayType = 'monday',
            'form.visitType.tuesday'   => $dayType = 'tuesday',
            'form.visitType.wednesday' => $dayType = 'wednesday',
            'form.visitType.thursday'  => $dayType = 'thursday',
            'form.visitType.friday'    => $dayType = 'friday',
            default => $dayType = 'false',
        };
        if (!$value) {
            if ($dayType !== 'false') {
                if (isset($this->form['timeFrame'][$dayType])) {
                    unset($this->form['timeFrame'][$dayType]);
                }
                if (isset($this->form['visitType'][$dayType])) {
                    unset($this->form['visitType'][$dayType]);
                }
                $this->counter[$dayType] = 1;
            }
        }
    }
    public function addFormCounter($counter)
    {
        // add first element if not exist
        isset($this->form['timeitrator'][$this->form['specialDaySetting']])        ? '' : $this->form['timeitrator'][$this->form['specialDaySetting']]        = 0;
        isset($this->form['specialTimeCounter'][$this->form['specialDaySetting']]) ? '' : $this->form['specialTimeCounter'][$this->form['specialDaySetting']] = 1;
        // add first element if not exist
        $this->dispatch('loadPersianDatePicker', true);
        $this->form[$counter] =  $this->form[$counter] + 1;
        $this->render();
    }
    public function removeFormCounter($counter)
    {
        $this->form[$counter] =  $this->form[$counter] - 1;
        $this->render();
    }
    public function addspecialDayTimeCounter($counter, $itrator)
    {
        $this->form[$counter][$itrator] =   $this->form[$counter][$itrator]  + 1;
        if ($counter == 'specialTimeCounter') {
            if (isset($this->form['specialDaytimeValues']) && isset($this->form['specialDaytimeValues'][$itrator])) {
                $lastArr = array_key_last($this->form['specialDaytimeValues'][$itrator]);
            } else {
                $lastArr = 0;
            }
            $lastArr == 1 ? $lastArr = $lastArr + 1 : '';
            $this->form['specialDaytimeValues'][$itrator][$lastArr + 1]['start'] = '00:00';
            $this->form['specialDaytimeValues'][$itrator][$lastArr + 1]['end']   = '00:00';
        }
        $this->render();
    }
    public function removespecialDayTimeCounter($counter, $itrator)
    {
        $this->form[$counter][$itrator] =   $this->form[$counter][$itrator]  - 1;
        if ($counter == 'specialTimeCounter') {
            $lastArr = array_key_last($this->form['specialDaytimeValues'][$itrator]);
            unset($this->form['specialDaytimeValues'][$itrator][$lastArr + 1]);
        }
        $this->render();
    }
    public function addCounter($day)
    {
        $this->counter[$day] = $this->counter[$day] + 1;
        $this->render();
    }
    public function removeCounter($day)
    {
        $this->counter[$day] = $this->counter[$day] - 1;
        if (isset($this->form['timeFrame'][$day]) && ($this->counter[$day] + 1) == count($this->form['timeFrame'][$day])) {
            array_pop($this->form['timeFrame'][$day]);
        }
        $this->render();
    }

    public function validateSpecialdate()
    {
        $specialDayRules = [];
        //check how many date are insert
        if (isset($this->form['specialDaydateValues']) && count($this->form['specialDaydateValues']) >= 1) {
            $specialDaydateValues = $this->form['specialDaydateValues'];
            $keyToValidate = count($this->form['specialDaydateValues']);
        } else {
            $keyToValidate = null;
        }
        if (isset($keyToValidate)) {
            for ($i = 0; $i < $keyToValidate; $i++) {
                if (isset($specialDaydateValues[$i]) && !empty($specialDaydateValues[$i])) {
                    for ($iterate = 0; $iterate <= $this->form['timeitrator'][$i]; $iterate++) {
                        $specialDayRules["form.specialDaytimeValues.{$i}.{$iterate}.start"] = 'required';
                        $specialDayRules["form.specialDaytimeValues.{$i}.{$iterate}.end"] =  'required';
                    }
                }
            }
        }

        // Merge the new rules with existing rules in the rules function
        return  $specialDayRules;
    }
    public function rules()
    {
        //validation for each day time frame
        $dayRules = [];
        foreach ($this->counter as $dayName => $counter) {
            //if that day is active
            if (isset($this->form['visitType'][$dayName]) && (isset($this->form['visitType'][$dayName]) == 'true')) {
                for ($i = 0; $i < $counter; $i++) {
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.start'] = 'required';
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.end']   = 'required';
                }
            }
        }
        $rules  = [
            'form.timeFrame'                      => 'required',
            'form.visitType.inPerson'             => 'required_without_all:form.visitType.online',
            'form.visitType.online'               => 'required_without_all:form.visitType.inPerson',
            'form.visitTime'                      => 'required',
            'form.minDayAvaialbe'                 => 'required|integer',
            'form.maxDayAvaialbe'                 => 'required|integer',
            'form.cancel.day'                     => 'required_if:form.cancel.status,true',
            'form.endAppointment.date'            => 'required_if:form.endAppointment.status,true',
            'form.segments.value'                 => 'required_if:form.segments.status,true',
            'form.monitoring.hour'                => 'required_if:form.monitoring.status,true',
            'form.startAppointment.date'          => 'required_if:form.startAppointment.status,true',
            'form.startAppointment.time'          => 'required_if:form.startAppointment.status,true',
            'form.payment.notPayingStatus'        => 'required_if:form.payment.status,true',
            'form.payment.inPerson.price'         => 'required_if:form.payment.inPerson.status,true',
            'form.payment.online.price'           => 'required_if:form.payment.online.status,true',
            'form.payment.voip.price'             => 'required_if:form.payment.voip.status,true',
            'form.operators.ids'                  => 'required_if:form.operators.status,true',
        ];
        if (isset($this->form['maxAvailabeAppointment']['status']) && $this->form['maxAvailabeAppointment']['status'] == true) {
            if (isset($this->form['visitType']['online']) && $this->form['visitType']['online'] == true) {
                if (! isset($this->form['maxAvailabeAppointmentOnline']) || (isset($this->form['maxAvailabeAppointmentOnline']) && $this->form['maxAvailabeAppointmentOnline'] == null)) {
                    $rules['form.maxAvailabeAppointment.eachDay'] = 'required';
                }
            } else {
                $rules['form.maxAvailabeAppointment.eachDay'] = 'required';
            }
        }
        $validateSpecialDate = $this->validateSpecialdate();
        return array_merge($dayRules,  $rules, $validateSpecialDate);
    }
    private function checkForUnsetTheCheckBoxes()
    {
        if (isset($this->form['specialDayTimeSetting']) && ($this->form['specialDayTimeSetting']) == false) {
            if (isset($this->form['specialDaydateValues'])) {
                foreach ($this->form['specialDaydateValues'] as $key => $date) {
                    $date =  $this->appointment_setting->times()->where('special_date', Verta::parse($date)->toCarbon())->first();
                    if (!empty($date)) {
                        $date->delete();
                    }
                }
            }
            unset($this->form['specialDaydateValues']);
        }
        if (isset($this->form['maxAvailabeAppointment']['status']) && ($this->form['maxAvailabeAppointment']['status']) == false) {
            if (isset($this->form['maxAvailabeAppointment']['eachDay'])) {
                unset($this->form['maxAvailabeAppointment']['eachDay']);
            }
            if (isset($this->form['maxAvailabeAppointment']['ForSecretery'])) {
                unset($this->form['maxAvailabeAppointment']['ForSecretery']);
            }
        }
        if (isset($this->form['monitoring']['status'])  && $this->form['monitoring']['status'] == false) {
            if (isset($this->form['monitoring']['hour'])) {
                unset($this->form['monitoring']['hour']);
                unset($this->form['monitoring']['status']);
            }
        }
        if (isset($this->form['cancel']['status'])  && $this->form['cancel']['status'] == false) {
            if (isset($this->form['cancel']['day'])) {
                unset($this->form['cancel']['day']);
                unset($this->form['cancel']['status']);
            }
        }
        if (isset($this->form['endAppointment']['status'])  && $this->form['endAppointment']['status'] == false) {
            if (isset($this->form['endAppointment']['date'])) {
                unset($this->form['endAppointment']['status']);
                unset($this->form['endAppointment']['date']);
            }
        }
        if (isset($this->form['payment']['status'])  && $this->form['payment']['status'] == false) {
            if (isset($this->form['payment'])) {
                unset($this->form['payment']);
            }
        }
        if (isset($this->form['payment']['online']['status'])  && $this->form['payment']['online']['status'] == false) {
            if (isset($this->form['payment']['online'])) {
                unset($this->form['payment']['online']);
            }
        }
        if (isset($this->form['payment']['voip']['status'])  && $this->form['payment']['voip']['status'] == false) {
            if (isset($this->form['payment']['voip'])) {
                unset($this->form['payment']['voip']);
            }
        }
        if (isset($this->form['payment']['inPerson']['status'])  && $this->form['payment']['inPerson']['status'] == false) {
            if (isset($this->form['payment']['inPerson'])) {
                unset($this->form['payment']['inPerson']);
            }
        }
        if (isset($this->form['interference']['status'])  && $this->form['interference']['status'] == false) {
            unset($this->form['interference']['status']);
        }
        if (isset($this->form['segments']['status'])  && $this->form['segments']['status'] == false) {
            if (isset($this->appointment_setting) && $this->appointment_setting->segments()->exists()) {
                $this->appointment_setting->segments()->detach();
            }
            unset($this->form['segments']['status']);
            unset($this->form['segments']['value']);
        }
        if (isset($this->form['startAppointment']['status'])  && $this->form['startAppointment']['status'] == false) {
            unset($this->form['startAppointment']['status']);
            unset($this->form['startAppointment']['date']);
            unset($this->form['startAppointment']['time']);
        }
        if (isset($this->form['operators']['status'])  && $this->form['operators']['status'] == false) {
            unset($this->form['operators']['status']);
            unset($this->form['operators']['ids']);
        }
    }
    public function saveSetting()
    {
        //if check box for each section is turned off , delete the data for it
        $this->checkForUnsetTheCheckBoxes();
        $this->validate();
        $endAppointmentTime   =  isset($this->form['endAppointment']['date']) ? Verta::parse($this->form['endAppointment']['date'])->toCarbon() : null;
        $startAppointmentTime =  isset($this->form['startAppointment']['date']) ? Verta::parse($this->form['startAppointment']['date'])->toCarbon()->setTime(substr($this->form['startAppointment']['time'], 0, 2,), substr($this->form['startAppointment']['time'], 3, 2)) : null;
        $detail = [
            AppointmentSetting::VISIT_TYPE_INPERSON                  => isset($this->form['visitType']['inPerson']) ? $this->form['visitType']['inPerson'] : null,
            AppointmentSetting::VISIT_TYPE_VOIP                      => isset($this->form['visitType']['voip']) ? $this->form['visitType']['voip'] : null,
            AppointmentSetting::VISIT_TYPE_ONLINE                    => isset($this->form['visitType']['online']) ? $this->form['visitType']['online'] : null,
            AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY   => isset($this->form['maxAvailabeAppointment']['eachDay']) ? $this->form['maxAvailabeAppointment']['eachDay'] : null,
            AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_FOR_SECRETERY     => isset($this->form['maxAvailabeAppointment']['ForSecretery']) ? $this->form['maxAvailabeAppointment']['ForSecretery'] : null,
            AppointmentSetting::MONITORTING_APPOINTMENT              => isset($this->form['monitoring']['hour']) ? $this->form['monitoring']['hour'] : null,
            AppointmentSetting::OPERATORS => [
                AppointmentSetting::STATUS => isset($this->form['operators']['status']) ? $this->form['operators']['status'] : false,
                AppointmentSetting::IDS    => isset($this->form['operators']['ids'])    ? $this->form['operators']['ids']    : null,
            ],
            AppointmentSetting::PAYMENT                              =>
            [
                AppointmentSetting::STATUS                           => isset($this->form['payment']['status']) ? $this->form['payment']['status']  : false,
                AppointmentSetting::NOT_PAYING_STATUS                => isset($this->form['payment']['notPayingStatus']) ?  $this->form['payment']['notPayingStatus']  : null,
                AppointmentSetting::ONLINE =>
                [
                    AppointmentSetting::STATUS                       => isset($this->form['payment']['online']['status']) ? $this->form['payment']['online']['status'] : null,
                    AppointmentSetting::PRICE                        => isset($this->form['payment']['online']['price'])  ? $this->form['payment']['online']['price']  : null,
                ],
                AppointmentSetting::VOIP  =>
                [
                    AppointmentSetting::STATUS                       => isset($this->form['payment']['voip']['status'])   ? $this->form['payment']['voip']['status']   : null,
                    AppointmentSetting::PRICE                        => isset($this->form['payment']['voip']['price'])    ? $this->form['payment']['voip']['price']    : null,
                ],
                AppointmentSetting::IN_PERSON  =>
                [
                    AppointmentSetting::STATUS                       => isset($this->form['payment']['inPerson']['status'])   ? $this->form['payment']['inPerson']['status']   : null,
                    AppointmentSetting::PRICE                        => isset($this->form['payment']['inPerson']['price'])    ? $this->form['payment']['inPerson']['price']    : null,
                ],
            ]
        ];

        if (isset($this->form['visitType']['online']) && $this->form['visitType']['online']) {
            // online appointment conditions
            $detail[AppointmentSetting::ONLINE_CAN_SEND_VOICE]              = isset($this->form['accessibility']['online']['can_send_voice']) && $this->form['accessibility']['online']['can_send_voice'];
            $detail[AppointmentSetting::MAX_ACTIVE_TIME_ONLINE_APPOINTMENT] = isset($this->form['onlinevisit']['time']) ? $this->form['onlinevisit']['time'] : null;
            $detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP]      = isset($this->form['maxAvailabeAppointmentOnline']) ? $this->form['maxAvailabeAppointmentOnline'] : null;
        } else {
            unset($detail[AppointmentSetting::ONLINE_CAN_SEND_VOICE]);
            $detail[AppointmentSetting::MAX_ACTIVE_TIME_ONLINE_APPOINTMENT] =  null;
            $detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP]      =  null;
        }

        $detail[AppointmentSetting::DONT_SHOW_TIMES] = [
            AppointmentSetting::DONT_SHOW_TIMES_STATUS  => $this->form['accessibility']['dont_show_times']['status'] == 'on',
            AppointmentSetting::DONT_SHOW_TIMES_MESSAGE => $this->form['accessibility']['dont_show_times']['message'] ?? null,
        ];

        // Clearing the message if it is inactive and does not display the message
        if (!$detail[AppointmentSetting::DONT_SHOW_TIMES][AppointmentSetting::DONT_SHOW_TIMES_STATUS]) {
            $detail[AppointmentSetting::DONT_SHOW_TIMES][AppointmentSetting::DONT_SHOW_TIMES_MESSAGE] = null;
        }

        // Temporary deactivation online
        $detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE] = [
            AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE_STATUS  => $this->form['accessibility']['disable_online']['status'] == 'on',
            AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE_MESSAGE => $this->form['accessibility']['disable_online']['message'] ?? null,
        ];

        if (!$detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE][AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE_STATUS]) {
            $detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE][AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE_MESSAGE] = null;
        }


        $updateOrCreateModel = [
            'user_id'               =>  $this->user->id,
            'service_id'            =>  $this->fetchData['service_id'],
            'place_id'              =>  $this->fetchData['place'],
            'time_for_visit'        =>  isset($this->form['visitTime']) ? $this->form['visitTime'] : null,
            'min_day_active'        =>  $this->form['minDayAvaialbe'],
            'max_day_active'        =>  $this->form['maxDayAvaialbe'],
            'cancellation_by_user'  =>  $this->form['cancel']['day'] ?? null,
            'last_day_active'       =>  $endAppointmentTime,
            'first_day_active'      =>  $startAppointmentTime,
            'active_payment'        =>  isset($this->form['payment']['online']) ? AppintmentSettingPaymentStatus::tryFrom($this->form['payment']['status']) : 0,
            'interference'          =>  isset($this->form['interference']['status'])  ? AppintmentSettingInterface::tryFrom($this->form['interference']['status']) : AppintmentSettingInterface::getDefault(),
            'active'                =>  ActiveEnum::tryFrom($this->form['avtive']),
            'detail'                =>  $detail,
        ];



        if ($this->isEdited) {
            if (isset($this->fetchData['service_id'])) {
                if ($this->isSpecialTimeEdited) {
                    //is user editing the times for special section
                    $this->appointment_setting->update($updateOrCreateModel);
                } else {
                    $this->appointment_setting = AppointmentSetting::create($updateOrCreateModel);
                }
            } else {
                $this->appointment_setting->update($updateOrCreateModel);
            }
        } else {
            $this->appointment_setting =   AppointmentSetting::create($updateOrCreateModel);
        }
        $appointment_setting_times =  $this->storeTimes();
        //store days and times
        if ($this->isEdited) {
            $this->appointment_setting->times()->delete();
            //is user editing the times
            if (isset($this->fetchData['service_id'])) {
                if ($this->isSpecialTimeEdited) {
                    //is user editing the times for special section
                    foreach ($appointment_setting_times as $objectForStore) {
                        $this->appointment_setting->times()->updateOrCreate($objectForStore);
                    }
                } else {
                    foreach ($appointment_setting_times as $objectForStore) {
                        $this->appointment_setting->times()->create($objectForStore);
                    }
                }
            } else {
                foreach ($appointment_setting_times as $objectForStore) {
                    $this->appointment_setting->times()->updateOrCreate($objectForStore);
                }
            }
        } else {
            //user is not in edit mode
            foreach ($appointment_setting_times as $objectForStore) {
                $this->appointment_setting->times()->updateOrCreate($objectForStore);
            }
        }
        if (isset($this->form['segments']['status']) && $this->form['segments']['status'] == true) {
            $this->appointment_setting->segments()->sync($this->form['segments']['value']);
        }
        // make appointment log
        CacheJob::dispatch($this->appointment_setting);

        return redirect()->route('admin.appointment.doctor.list')->with('success', 'تنظیمات با موفقیت ذخیره شد');
    }
    private function storeTimes()
    {
        $appointment_setting_times = [];
        foreach ($this->form['timeFrame'] as $dayName => $timeFrameForEachDay) {
            foreach ($timeFrameForEachDay as $key => $timeFrame) {
                if (isset($this->form['visitType'][$dayName]) && $this->form['visitType'][$dayName] == 'ture') {
                    $appointment_setting_times[] = [
                        'day_number' => AppintmentSettingDayNumber::getConstant($dayName),
                        'start_at'  => $timeFrame['start'],
                        'end_at'  => $timeFrame['end'],
                    ];
                }
            }
        }
        $specialTimes = [];
        if (isset($this->form['specialDaydateValues'])) {
            foreach ($this->form['specialDaytimeValues'] as $index => $eachdayTimeArray) {
                $carbon = Verta::parse($this->form['specialDaydateValues'][$index])->toCarbon();
                foreach ($eachdayTimeArray as $startAndEndDates) {
                    $specialTimes[] = [
                        'day_number' => AppintmentSettingDayNumber::getConstant(strtolower($carbon->format('l'))),
                        'start_at'  => $startAndEndDates['start'],
                        'end_at'    => $startAndEndDates['end'],
                        'special_date' => $carbon->format('Y/m/d'),
                    ];
                }
            }
        }
        return array_merge($specialTimes, $appointment_setting_times);
    }
    private function fillTheForm()
    {
        if (empty($this->fetchData['service_id'])) {
            $apSet = AppointmentSetting::where('user_id', $this->fetchData['user']->id)
                ->whereNull('service_id')
                ->first();
        } else {
            $apSet = AppointmentSetting::where('user_id', $this->fetchData['user']->id)
                ->where('service_id', $this->fetchData['service_id'])
                ->first();
            $this->isSpecialTimeEdited = true;
            if (empty($apSet)) {
                $apSet = AppointmentSetting::where('user_id', $this->fetchData['user']->id)->whereNull('service_id')->first();
                $this->isSpecialTimeEdited = false;
            }
        }
        $this->appointment_setting = $apSet;

        $this->fillTheTime($apSet);
        $this->form['visitType']['inPerson']             = $apSet->detail['visit_type_inPerson'] ?? false;
        $this->form['visitType']['voip']                 = $apSet->detail['visit_type_voip'] ?? false;
        $this->form['visitType']['online']               = $apSet->detail['visit_type_online'] ?? false;
        $this->form['visitTime']                         = $apSet->time_for_visit;
        $this->form['minDayAvaialbe']                    = $apSet->min_day_active;
        $this->form['maxDayAvaialbe']                    = $apSet->max_day_active;
        $this->form['maxAvailabeAppointment']['eachDay'] = $apSet->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY];
        $this->form['maxAvailabeAppointment']['ForSecretery'] = $apSet->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_FOR_SECRETERY] ?? null;
        $this->form['cancel']['day']                     = $apSet->cancellation_by_user ?? null;
        $this->form['avtive']                            = $apSet->active == ActiveEnum::ACTIVE ? true : false;
        if (isset($apSet->last_day_active)) {
            $this->form['endAppointment']['date'] = verta($apSet->last_day_active)->format('Y/m/d');
        }
        if (isset($apSet->first_day_active)) {
            $this->form['startAppointment']['date'] = verta($apSet->first_day_active)->format('Y/m/d');
            $this->form['startAppointment']['time'] = verta($apSet->first_day_active)->format('H:i');
            $this->form['startAppointment']['status'] = true;
        }
        if (isset($apSet->active_payment)) {
            $this->form['payment']['status'] = $apSet->active_payment;
        }
        if (isset($apSet->interference)) {
            $this->form['interference']['status'] = $apSet->interference;
        }
        if (isset($apSet->detail[AppointmentSetting::PAYMENT])) {
            if (isset($apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::STATUS])) {
                $this->form['payment']['status']  = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::STATUS];
            }
            if (isset($apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::NOT_PAYING_STATUS])) {
                $this->form['payment']['notPayingStatus']  = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::NOT_PAYING_STATUS];
            }
            if (isset($apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::ONLINE])) {
                $this->form['payment'][AppointmentSetting::ONLINE][AppointmentSetting::STATUS] = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::ONLINE][AppointmentSetting::STATUS];
                $this->form['payment'][AppointmentSetting::ONLINE][AppointmentSetting::PRICE] = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::ONLINE][AppointmentSetting::PRICE];
            }
            if (isset($apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::VOIP])) {
                $this->form['payment'][AppointmentSetting::VOIP][AppointmentSetting::STATUS] = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::VOIP][AppointmentSetting::STATUS];
                $this->form['payment'][AppointmentSetting::VOIP][AppointmentSetting::PRICE] = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::VOIP][AppointmentSetting::PRICE];
            }
            if (isset($apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::IN_PERSON])) {
                $this->form['payment'][AppointmentSetting::IN_PERSON][AppointmentSetting::STATUS] = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::IN_PERSON][AppointmentSetting::STATUS];
                $this->form['payment'][AppointmentSetting::IN_PERSON][AppointmentSetting::PRICE] = $apSet->detail[AppointmentSetting::PAYMENT][AppointmentSetting::IN_PERSON][AppointmentSetting::PRICE];
            }
        }

        if (isset($apSet->detail[AppointmentSetting::ONLINE_CAN_SEND_VOICE]) && $apSet->detail[AppointmentSetting::ONLINE_CAN_SEND_VOICE]) {
            $this->form['accessibility']['online']['can_send_voice'] = true;
        } else {
            $this->form['accessibility']['online']['can_send_voice'] = false;
        }

        // dont show times
        if (isset($apSet->detail[AppointmentSetting::DONT_SHOW_TIMES])) {
            $this->form['accessibility']['dont_show_times']['status'] = isset($apSet->detail[AppointmentSetting::DONT_SHOW_TIMES][AppointmentSetting::STATUS]) && $apSet->detail[AppointmentSetting::DONT_SHOW_TIMES][AppointmentSetting::STATUS] == 'on';
            $this->form['accessibility']['dont_show_times']['message'] = $apSet->detail[AppointmentSetting::DONT_SHOW_TIMES][AppointmentSetting::DONT_SHOW_TIMES_MESSAGE] ?? null;
        }

        //Temporary deactivation
        if (isset($apSet->detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE])) {
            $this->form['accessibility']['disable_online']['status'] = isset($apSet->detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE][AppointmentSetting::STATUS]) && $apSet->detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE][AppointmentSetting::STATUS] == 'on';
            $this->form['accessibility']['disable_online']['message'] = $apSet->detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE][AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE_MESSAGE] ?? null;
        }



        if (isset($apSet->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_FOR_SECRETERY])) {
            $this->form['maxAvailabeAppointment']['ForSecretery'] = $apSet->detail[AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_FOR_SECRETERY];
        }
        if (isset($apSet->detail[AppointmentSetting::OPERATORS])) {
            if ($apSet->detail[AppointmentSetting::OPERATORS][AppointmentSetting::STATUS] == true) {
                $this->form['operators']['status'] = true;
                $this->form['operators']['ids'] = $apSet->detail[AppointmentSetting::OPERATORS][AppointmentSetting::IDS];
            }
        }
        if (!$apSet->segments->isEmpty()) {
            $this->form['segments'][AppointmentSetting::STATUS] = true;
            $this->form['segments']['value'] = $apSet->segments->first()->id;
        }
        if (isset($apSet->detail[AppointmentSetting::MONITORTING_APPOINTMENT])) {
            $this->form['monitoring']['status'] = true;
            $this->form['monitoring']['hour'] = $apSet->detail[AppointmentSetting::MONITORTING_APPOINTMENT];
        }
    }
    private function fillTheTime($apSet)
    {
        foreach ($apSet->times()->whereNull('special_date')->get()->groupBy('day_number') as $dayNumber => $eachDayColleciton) {
            $this->form['visitType'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()] = true;
            $this->counter[AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()] = count($eachDayColleciton);
            foreach ($eachDayColleciton as $iterator => $value) {
                $this->form['timeFrame'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()][$iterator]['start'] = $value->start_at;
                $this->form['timeFrame'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()][$iterator]['end'] = $value->end_at;
            }
        }
        // special_date
        if ($apSet->times()->whereNotNull('special_date')->get()->isNotEmpty()) {
            $sorted_special_day = $apSet->times()->whereNotNull('special_date')->get()->groupBy('special_date');
            $this->form['specialDaySetting'] = count($sorted_special_day);
            $i = 0;
            foreach ($sorted_special_day  as $spDayNum => $SpEachDayColleciton) {

                $this->form['specialDaydateValues'][$i] = verta($SpEachDayColleciton->first()->special_date)->format('Y/m/d');
                foreach ($SpEachDayColleciton as $Spiterator => $Spvalue) {
                    $this->form['specialDaytimeValues'][$i][$Spiterator]['start'] = $Spvalue->start_at;
                    $this->form['specialDaytimeValues'][$i][$Spiterator]['end']   = $Spvalue->end_at;
                    $this->form['specialTimeCounter'][$i] = count($sorted_special_day[$spDayNum]);
                }
                $this->form['timeitrator'][$i] = 0;
                $i = $i + 1;
            }
        }
        // dd($this->form['specialDaytimeValues'],$this->form['specialTimeCounter']);
    }
    public function mount()
    {
        $this->fetchData['user']            =  request()->route('user');
        $this->fetchData['service_id']      =  request()->route('service');
        $this->fetchData['place']           =  request()->route('place');
        $this->fetchData['operator']        = User::operators();
        if (isset($this->fetchData['place'])) {
            $this->fetchData['place'] =   $this->fetchData['place']->id;
        }
        if (!empty($this->fetchData['user'])) {
            $this->fetchData['doctor'] =  $this->fetchData['user'];
        } else {
            return redirect()->route('admin.appointment.doctor.list')->with('error', 'پزشک مورد نظر یافت نشد');
        }
        //  check if the setting for sections exist
        //   wich means this section is not the first time that set setting for
        $generalSetting_exists = AppointmentSetting::whereNull('service_id')
            ->whereNull('place_id')
            ->where('user_id', $this->fetchData['user']->id)
            ->exists();
        $check_Setting_exist = false;
        if ($generalSetting_exists && AppointmentSetting::where('user_id', $this->fetchData['user']->id)->get()->isNotEmpty()) {
            //setting exist
            $check_Setting_exist = true;
        }
        if (session()->has('resetTheSetting')) {
            if ($generalSetting_exists) {
                $check_Setting_exist = false;
                $this->isEdited = true;
                $this->fillTheForm();
            }
        }
        // if ($check_Setting_exist) {
        //     return redirect()->route('admin.appointment.specialsection', ['user' => $this->fetchData['user']]);
        // }
        if (AppointmentSegment::exists()) {
            $this->fetchData['segments'] = AppointmentSegment::all();
        }
    }

    public function render()
    {

        return view('appointmentsetting::livewire.general-setting.general-setting');
    }
}
