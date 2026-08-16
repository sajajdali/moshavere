<?php

namespace Modules\AppointmentUser\Livewire\Admin\AddAppointment\Modal;

use Livewire\Component;
use Livewire\Attributes\Url;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Modules\AppointmentUser\Livewire\Admin\AddAppointment\SpecificDayAvailableAppointment;

class ServiceAndDoctorModal extends Component
{
    /*
    this modal is beeing used in :
    SpecificDayAvailableAppointment::class
    */
    public $step = 1;

    public array $fetchData = [];
    public array $form = [];

    #[Url]
    public array $search =  [
        'doctor' => null,
        'service' => null,
    ];

    public $docSection;

    public function closeModal()
    {
        $this->step = 1;
        $this->search = [
            'doctor' => null,
        ];
    }

    //after seleced one of the doctors
    public function doctorSelected($id)
    {
        $this->docSection = User::find($id);
        $this->fetchData['service'] = $this->docSection->service()->orderBy('parent_id', 'asc')->get()->take(10);
        $this->step = $this->step + 1;
        $this->render();
    }
    //after seleced one of the services
    //final function
    public function selectSection()
    {
        if (!isset($this->form['service'])) {
            return $this->addError('selectService', 'لطفا یک سرویس را انتخاب کنید');
        }
        $this->form['service_id'] = $this->form['service'];
        $this->form['doctor_id'] = $this->docSection->id;
        if ($this->fetchData['places']->isNotEmpty() && $this->fetchData['places']->count() == 1) {
            return  $this->selectplace($this->fetchData['places']->first()->id);
        } else {
            $this->step = 3;
        }
        // $this->dispatch('docAndSection', section: $section_id, doctor: $doctor_id);
        // $this->dispatch('closeModal', true);
    }
    public function selectplace($id)
    {
        $this->form['place_id'] = $id;
        $special_setting_for_appointment = AppointmentSetting::where('user_id', $this->form['doctor_id'])
            ->where('service_id', $this->form['service_id'])
            ->where('place_id', $this->form['place_id'])->first();
        if (!empty($special_setting_for_appointment)) {
            $this->form['app_id'] = $special_setting_for_appointment->id;
        } else {
            $special_setting_for_appointment = AppointmentSetting::where('user_id', $this->form['doctor_id'])
                ->whereNull('service_id')
                ->whereNull('place_id')
                ->first();
        }
        if (!empty($special_setting_for_appointment)) {
            $this->form['app_id'] = $special_setting_for_appointment->id;
            $this->dispatch('closeModal', true);
            $this->dispatch('docHasChange', appId: $this->form['app_id'], serviceId: $this->form['service_id'], placeId: $this->form['place_id']);
        } else {
            return redirect()->route('admin.appointment_user.addApp')->with('error', 'تنظیمات حضور یافت نشد');
        }
    }
    public function searchDocAndSection()
    {
        if ($this->step == 2) {
            $this->fetchData['service'] = $this->docSection->service()->when(!empty($this->search['service']), function ($q) {
                return $q->where('title', 'LIKE', "%{$this->search['service']}%");
            })->get();
        }
        $this->render();
    }
    //close modal button
    public function ignoreSearch()
    {
        $this->search = [
            'doctor'  => null,
            'service' => null,
        ];
        if ($this->step == 2) {
            $this->fetchData['service'] =  $this->docSection->service->take(10);
        }
        $this->render();
    }

    public function mount()
    {
        $this->fetchData['places'] = Place::all();
    }
    public function render()
    {
        if ($this->step == 1) {
            if (User::doctors()->count() == 1) {
                $this->doctorSelected(User::doctors()->first()->id);
            }
            $query = User::doctors_query()->when(!empty($this->search['doctor']), function ($query) {
                return $query->where(function ($q) {
                    $q->whereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::FIRST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['doctor']}%"],
                        ]);
                    })->orWhereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::LAST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['doctor']}%"],
                        ]);
                    });
                });
            })->take(10)
                ->get();
        } else {
            $query = User::doctors()->take(10);
        }

        return view(
            'appointmentuser::livewire.admin.add-appointment.modal.service-and-doctor-modal',
            ['doctors' =>  $query]
        );
    }
}
