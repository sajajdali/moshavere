<?php

namespace Modules\User\Livewire\Admin\User\Oprator;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;

class OpratorTimeSetting extends Component
{

    public array $form = [];
    public array $fetchData = [];
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

    #[Locked]
    public bool $isEdited = false;

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

    public function rules()
    {
        $dayRules = [];
        foreach ($this->counter as $dayName => $counter) {
            // if that day is active
            if (isset($this->form['visitType'][$dayName]) && ($this->form['visitType'][$dayName] == 'true')) {
                for ($i = 0; $i < $counter; $i++) {
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.start'] = 'required';
                    $dayRules['form.timeFrame.' . $dayName . '.' . $i . '.end']   = 'required';
                }
            }
        }
        $rules = [
            'form.timeFrame'  => 'required',
        ];
        return array_merge($dayRules, $rules);
    }

    public function storeTimes()
    {
        $this->validate();
        if ($this->isEdited) {
            $this->fetchData['oprator']->operatorTimes()->delete();
        }
        $appointment_setting_times = [];
        foreach ($this->form['timeFrame'] as $dayName => $timeFrameForEachDay) {
            foreach ($timeFrameForEachDay as $key => $timeFrame) {
                if (isset($this->form['visitType'][$dayName]) && $this->form['visitType'][$dayName] == 'ture') {
                    $appointment_setting_times[] = [
                        'day_number' => AppintmentSettingDayNumber::getConstant($dayName),
                        'start_at'   => $timeFrame['start'],
                        'end_at'     => $timeFrame['end'],
                    ];
                }
            }
        }
        foreach ($appointment_setting_times as $objectForStore) {
            $this->fetchData['oprator']->operatorTimes()->updateOrCreate($objectForStore);
        }
        return redirect()->route('admin.user.index')->with('success', 'تنظیمات روز های  حضور اپراتور باموفقیت ثبت شد');
    }
    private function fillTheTime()
    {
        foreach ($this->fetchData['oprator']->operatorTimes()->get()->groupBy('day_number') as $dayNumber => $eachDayColleciton) {
            $this->form['visitType'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()] = true;
            $this->counter[AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()] = count($eachDayColleciton);
            foreach ($eachDayColleciton as $iterator => $value) {
                $this->form['timeFrame'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()][$iterator]['start'] = $value->start_at;
                $this->form['timeFrame'][AppintmentSettingDayNumber::tryFrom($dayNumber)->getEnName()][$iterator]['end'] = $value->end_at;
            }
        }
    }

    public function mount()
    {
        $this->fetchData['oprator'] = User::findOrFail(request()->route('user'));
        if ($this->fetchData['oprator']->operatorTimes()->exists()) {
            $this->fillTheTime();
            $this->isEdited = true;
        }
    }
    public function render()
    {
        return view('user::livewire.admin.user.oprator.oprator-time-setting');
    }
}
