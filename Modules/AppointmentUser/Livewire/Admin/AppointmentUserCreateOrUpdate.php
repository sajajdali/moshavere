<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Livewire\Component;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Spatie\Permission\Models\Role;

class AppointmentUserCreateOrUpdate extends Component
{
    public array $search = [];
    public array $form = [
        'doctorSelected'    => null,
        'doctorServices' => []
    ];
    public $modalDate = null;
    public function searchDoctors()
    {
        $this->render();
    }
    public function searchSection()
    {
        $this->render();
    }

    //pass data to the modal after doctor has been selected
    public function lunchDocModal(User $doctor)
    {
        $this->dispatch('lunchModal', true);
        $this->form['doctorSelected'] = $doctor;
        $this->form['doctorServices'] = $doctor->service;
    }


    //select section from modal
    public function addAppointment($sectionId)
    {
        return redirect()->route('admin.appointment.add.setTime',['doctorId' => $this->form['doctorSelected'] , 'sectionId' => $sectionId]) ;
    }
    public function render()
    {
        $doctors = Role::find(3)->users()
            ->when(isset($this->search['doctors']) && !empty($this->search['doctors']), function ($query) {
                return $query->whereHas('metas', function ($q) {
                    $q->where([
                        ['meta_key', UserMetaEnum::FIRST_NAME],
                        ['meta_value', 'LIKE', "%{$this->search['doctors']}%"],
                    ]);
                });
            })->orderByDesc('id')->get();

        $setctions = null;
        return view('appointmentuser::livewire.admin.appointment-user-create-or-update', [
            'doctors' => $doctors,
            'sections' => $setctions,
        ]);
//        return view('appointmentuser::livewire.admin.appointment-user-create-or-update');

    }
}
