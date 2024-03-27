<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Livewire\Component;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Modules\Service\app\Models\Service;

class AppointmentUserCreateOrUpdate extends Component
{
    public array $search = [];
    public array $form = [
        'doctorSelected'    => null,
        'doctorServices' => [],
        'modalTitle' => '',
        'modalStatus' => [
            'selectPlace'  => false,
            'selectDoctor' => false,
            'selectService' => false,
        ],
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
    public function PlaceModal(User $doctor)
    {
        $this->form['modalStatus']['selectPlace'] =  true;
        $this->form['doctorSelected'] = $doctor;
        $this->form['place'] = $doctor->places;
        $this->form['doctorServices'] = $doctor->service;
        $this->form['modalTitle'] = 'انتخاب مطب';
        // if (isset($this->form['place']) && $this->form['place']->count() == 1) {
        //     return $this->lunchDocModal();
        // }
        $this->lunchmodal();
    }
    public function PlaceSelectred(Place $place) {
        $this->form['selectedPlace'] = $place->id ;
        $this->lunchDocModal();
    }

    public function lunchDocModal()
    {
        $this->form['modalStatus']['selectDoctor'] =  true;
        $this->form['modalTitle'] = 'انتخاب پزشک';
        $this->lunchmodal();
    }
    public function lunchServiceDocModal(Service $service)
    {
        $this->form['modalStatus']['selectService'] =  true;
        $this->form['ServiceDoctors'] = $service->user;
        $this->form['modalTitle'] = 'انتخاب بخش';
        $this->lunchmodal();
    }
    private function lunchmodal()
    {
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
            ->when(isset($this->search['searchService']) && !empty($this->search['searchService']), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search['searchService']}%");
            })->orderByDesc('id')->get();;
        return view('appointmentuser::livewire.admin.appointment-user-create-or-update', [
            'doctors' => $doctors,
            'Services' => $Services,
        ]);
    }
}
