<?php

namespace Modules\Reminder\Livewire\Admin\Reminder;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Illuminate\Support\Arr;
use Modules\User\Entities\User;
use Modules\Service\app\Models\Service;
use Modules\Reminder\app\Models\Reminder;
use Modules\Reminder\Enum\ReminderStatusEnum;

class UpdateOrCreate extends Component
{

    public array $fetchData = ['parametrCounter' => 0];
    public array $form = [
        'doctors' => 'all',
        'sendType' => ReminderStatusEnum::SMS,
        'sendDate' => 'sameDay',
        'active' => 1,
        'timeSend' => 2,
    ];
    public function updateSpecificPRoperties()
    {
        if (isset($this->form['specificDoctors'])) {
            unset($this->form['specificDoctors']);
        }
    }
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
        $custumrules = [];
        if (isset($this->form['sendType'])) {
            match ($this->form['sendType']) {
                ReminderStatusEnum::SMS => $custumrules['form.smsTemplateName'] = 'required',
                ReminderStatusEnum::CALL => $custumrules['form.callAnnouncment'] = 'required',
                ReminderStatusEnum::NOTIFICATION => $custumrules['form.notificationText'] = 'required',
            };
        }
        $generalRuls = [
            'form.doctors'          => 'required',
            'form.specificDoctors'  => 'required_if:form.doctors,specificDoctor',
            'form.sendDate'         => 'required',
            'form.specificDay'      => 'required_if:form.sendDay,selectedDate',
            'form.timeSend'         => 'required',
        ];
        return array_merge($custumrules , $generalRuls) ;
    }
    public function messages()
    {
        return [
            'form.specificDoctors.required_if' => 'لطفا پزشک مورد نظر را انتخاب کنید',
            'form.smsTemplateName.required' => 'لطفا نام قالب پیامکی را وارد کنید ',
            'form.notificationText.required' => 'لطفا متن نوتیفیکشن را وارد کنید ',
            'form.callAnnouncment.required' => 'لطفا عنوان قالب پیام تلفنی را وارد کنید ',
            'form.timeSend.required'        => 'لطفا ساعت ارسال را وارد کنید',
        ];
    }
    public function storeReminder()
    {

        $this->validate();
        $this->StoreDBReminder();
    }
    private function StoreDBReminder()
    {

        if (isset($this->form['service']) && $this->form['service'] != null) {
            $service = Service::find($this->form['service']);
        }
        $status = $this->form['sendType'];
        $body = match ($status) {
            ReminderStatusEnum::SMS          => $this->form['smsTemplateName'],
            ReminderStatusEnum::NOTIFICATION => $this->form['notificationText'],
            ReminderStatusEnum::CALL         => $this->form['callAnnouncment'],
        };
        $parameter = isset($this->form['parametr']) ? $this->form['parametr'] : null;
        if (isset($this->form['service']) &&  $this->form['service'] != null) {
            $service = Service::find($this->form['service']);
        }
        $model = [
            'status'       => $status,
            'body'         => $body,
            'parameters'   => $parameter,
            'doctors'      => isset($this->form['specificDoctors']) ? $this->form['specificDoctors'] : null,
            'send_day'     => $this->form['sendDate'] == 'sameDay' ? null : $this->form['send_at_specific_date'],
            'send_time'    => $this->form['timeSend'],
            'active'       => ActiveEnum::tryFrom($this->form['active']),
        ];
        if (isset($this->fetchData['reminder'])) {
            if ($status == ReminderStatusEnum::CALL && isset($parameter)) {
                $model['parameters'] = null ;
            }
            if (isset($service)) {
                if (
                    is_null($this->fetchData['reminder']->reminderable_id) &&
                    is_null($this->fetchData['reminder']->reminderable_type)
                ) {
                    $service->reminder()->save($this->fetchData['reminder']);
                } else {
                    // Update the Reminder through the relationship
                    $this->fetchData['reminder']->update($model);
                }
            } else {
                if (
                    is_null($this->fetchData['reminder']->reminderable_id) &&
                    is_null($this->fetchData['reminder']->reminderable_type)
                ) {
                    $this->fetchData['reminder']->update($model);
                } else {
                    $this->fetchData['reminder']->update(array_merge($model, ['reminderable_type' => null, 'reminderable_id' => null]));
                }
            }
        } else {
            if (isset($service)) {
                $service->reminder()->create($model);
            } else {
                Reminder::create($model);
            }
        }
        return redirect()->route('admin.reminder.list')->with('success', 'یادآور با موفقیت اضافه شد');
    }
    private function fillTheInputs()
    {
        if (!empty($this->fetchData['reminder']->reminderable)) {
            $this->form['service'] = $this->fetchData['reminder']->reminderable->id;
        }
        $this->form['sendType'] = $this->fetchData['reminder']->status;
        $body = match ($this->form['sendType']) {
            ReminderStatusEnum::SMS          => $this->form['smsTemplateName']  = $this->fetchData['reminder']->body,
            ReminderStatusEnum::NOTIFICATION => $this->form['notificationText'] = $this->fetchData['reminder']->body,
            ReminderStatusEnum::CALL         => $this->form['callAnnouncment']  = $this->fetchData['reminder']->body,
        };
        if (!empty($this->fetchData['reminder']->parameters)) {
            $this->fetchData['parametrCounter'] = count($this->fetchData['reminder']->parameters) - 1;
            $this->form['parametr'] = $this->fetchData['reminder']->parameters;
        }
        if (!empty($this->fetchData['reminder']->doctors)) {
            $this->form['doctors'] = 'specificDoctor';
            $this->form['specificDoctors'] = $this->fetchData['reminder']->doctors;
        }
        $this->form['sendDate'] =  $this->fetchData['reminder']->send_day == null ? 'sameDay' : 'selectedDate';
        $this->form['sendDate'] == 'sameDay' ?  $this->form['send_at_specific_date'] =  $this->fetchData['reminder']->send_day : '';
        $this->form['send_at_specific_date'] = $this->fetchData['reminder']->send_day;
        $this->form['timeSend'] = $this->fetchData['reminder']->send_time;
    }
    public function booted()
    {
        $this->dispatch('loadjs', true);
    }
    public function mount()
    {
        if (request()->has('reminder')) {
            $this->fetchData['reminder'] = Reminder::find(request()->get('reminder'));
            if (!empty($this->fetchData['reminder'])) {
                $this->fillTheInputs();
            }
        }
        $this->fetchData['doctors'] = User::doctors();
        $this->fetchData['services'] = Service::all();
    }
    public function render()
    {
        return view('reminder::livewire.admin.reminder.update-or-create');
    }
}
