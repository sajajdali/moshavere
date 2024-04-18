<?php

namespace Modules\Reminder\Livewire\Admin\Reminder;

use Livewire\Component;
use Modules\User\Entities\User;
use Modules\Service\app\Models\Service;

class UpdateOrCreate extends Component
{
    public array $fetchData = ['parametrCounter' => 0];
    public array $form = [
        'doctors' => 'all',
        'sendType' => 'sms',
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
            'form.doctors' => 'required',
            'form.specificDoctors'  => 'required_if:form.doctors,specificDoctor',
            'form.smsTemplateName'  => 'required_if:form.sendType,sms',
            'form.callAnnouncment'  => 'required_if:form.sendType,call',
            'form.notificationText' => 'required_if:form.sendType,notification',
            'form.parametr'         => 'required_if:form.sendType,sms,notification',
        ];
    }
    public function storeReminder()
    {
        $this->validate();
        dd($this->form);
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
