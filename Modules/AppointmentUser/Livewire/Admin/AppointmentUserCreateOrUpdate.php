<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Livewire\Component;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;
use Modules\Service\app\Models\Service;

class AppointmentUserCreateOrUpdate extends Component
{
    public array $search = [];
    public array $form = [
        'doctorSelected'    => null,
        'doctorServices' => [],
        'modalStatus' => null,
    ];
    public array $fetchData = [];
    public $modalDate = null;
    public function searchDoctors()
    {
        $this->render();
    }
    public function ignoreSearch()
    {
        $this->search = [];
        $this->render();
    }
    public function searchService()
    {

        $this->render();
    }

    //pass data to the modal after doctor has been selected
    public function lunchDocModal(User $doctor)
    {
        $this->dispatch('lunchModal', true);
        $this->form['modalStatus'] = 'doctorSelected';
        $this->form['doctorSelected'] = $doctor;
        $this->form['doctorServices'] = $doctor->service;
    }
    public function lunchServiceDocModal(Service $service)
    {
        $this->form['modalStatus'] = 'serviceSelected';
        $this->form['ServiceDoctors'] = $service->user;
        $this->dispatch('lunchModal', true);
    }


    //select section from modal
    public function addAppointment($id)
    {
        if ($this->form['modalStatus'] == 'doctorSelected') {
            $serviceid = $id;
            $doctorid =  $this->form['doctorSelected'];
        } else {
            $doctorid  = $id;
            $serviceid =  $this->form['doctorSelected'];
        }
        return redirect()->route('admin.appointment.add.setTime', ['doctorId' => $doctorid, 'sectionId' => $serviceid]);
    }

    public function mount()
    {
        if (!Service::exists()) {
            return redirect()->route('admin.service.list')->with('error', 'لطفا حداقل یک بخش به سیستم اضافه کنید');
        }
    }
    public function render()
    {

        $doctors = User::doctors_query()
            ->when(isset($this->search['doctors']) && !empty($this->search['doctors']), function ($query) {
                return $query->whereHas('metas', function ($q) {
                    $q->where([
                        ['meta_key', UserMetaEnum::FIRST_NAME],
                        ['meta_value', 'LIKE', "%{$this->search['doctors']}%"],
                    ]);
                });
            })->orderByDesc('id')->get();

        $Services = Service::query()
            ->when(isset($this->search['searchService']) && !empty($this->search['doctors']), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search['searchService']}%");
            })->orderByDesc('id')->get();;
        return view('appointmentuser::livewire.admin.appointment-user-create-or-update', [
            'doctors' => $doctors,
            'Services' => $Services,
        ]);
    }
}
