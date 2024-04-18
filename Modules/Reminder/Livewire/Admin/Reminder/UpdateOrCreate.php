<?php

namespace Modules\Reminder\Livewire\Admin\Reminder;

use Livewire\Component;
use Modules\Reminder\app\Models\Reminder;
use Modules\Reminder\Enum\ReminderStatusEnum;
use Modules\User\Entities\User;
use Modules\Service\app\Models\Service;

class UpdateOrCreate extends Component
{
    public array $fetchData = ['parametrCounter' => 0];
    public array $form = [
        'doctors' => 'all',
        'sendType' => ReminderStatusEnum::SMS,
        'sendDate' => 'sameDay',
    ];
    public function addMoreParam()
    {
        $this->fetchData['parametrCounter'] = $this->fetchData['parametrCounter'] + 1;
    }
    public function removeParam($i)
    {
        if (isset($this->form['param'][$i])) {
            unset($this->form['param'][$i]);
            $this->form['param'] = array_values($this->form['param']);
        }
        $this->fetchData['parametrCounter'] = $this->fetchData['parametrCounter'] - 1;
    }
    public function updated($property)
    {
        if ($property === 'form.service') {
            if ($this->form['service'] !== 'null') {
                $service = Service::find($this->form['service']);
                $this->fetchData['doctors'] = $service->user;
            } else {
                $this->fetchData['doctors'] = Service::all();
            }
        }
    }
    public function rules()
    {
        return [
            'form.service'          => 'required',
            'form.doctors'          => 'required',
            'form.specificDoctors'  => 'required_if:form.doctors,specificDoctor',
            'form.smsTemplateName'  => 'required_if:form.sendType,sms',
            'form.callAnnouncment'  => 'required_if:form.sendType,call',
            'form.notificationText' => 'required_if:form.sendType,notification',
            'form.sendDay'          => 'required',
            'form.specificDay'      => 'required_if:form.sendDay,selectedDate',
            'form.timeSend'         => 'required',
        ];
    }
    public function messages(){
        return [
            'form.specificDoctors.required_if' => 'لطفا پزشک مورد نظر را انتخاب کنید',
            'form.smsTemplateName.required_if' => 'لطفا نام قالب پیامکی را وارد کنید ',
            'form.notificationText.required_if' => 'لطفا متن نوتیفیکشن را وارد کنید ',
            'form.callAnnouncment.required_if' => 'لطفا عنوان قالب پیام تلفنی را وارد کنید ',
            'form.timeSend.required'        => 'لطفا ساعت ارسال را وارد کنید',
        ];
    }
    public function storeReminder()
    {
        $this->validate();
        $this->StoreDBReminder();
    }
    private function StoreDBReminder() {
        if($this->form['service'] != null) {
                $service = Service::find($this->form['service']);
        }
        $model = [
            'status' => ReminderStatusEnum::tryFrom($this->form['sendType'])
        ];
        Reminder::UpdateOrCreate($model);
    }

    public function booted()
    {
        $this->dispatch('loadjs', true);
    }
    public function mount()
    {
        $this->fetchData['doctors'] = User::doctors();
        $this->fetchData['services'] = Service::all();
    }
    public function render()
    {
        return view('reminder::livewire.admin.reminder.update-or-create');
    }
}
