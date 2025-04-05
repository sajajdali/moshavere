<?php

namespace Modules\AppointmentSetting\Livewire\Modal;

use Livewire\Component;
use Livewire\Attributes\Url;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;
use Modules\AppointmentSetting\Livewire\GeneralSetting\SpecialSectionSetting;
use Modules\AppointmentSetting\Livewire\AddAppointment\SpecificDayAvailableAppointment;

class ServiceAndDoctorModal extends Component
{
    /*
    this modal is beeing used in :
    SpecificDayAvailableAppointment::class
    */
    public $step = 1;

    public array $fetchData = [];

    #[Url]
    public $search;

    public $docSection;

    public function dismisMOdal()
    {
        $this->step = 1;
        $this->search = null;
    }

    //after seleced one of the doctors
    public function showRelatedSection($id)
    {
        $this->docSection = User::find($id);
        $this->fetchData['sections'] = $this->docSection->service->take(10);
        $this->search = null;
        $this->step = $this->step + 1;
        $this->render();
    }
    //after seleced one of the services
    //final function
    public function selectSection($id)
    {
        $section_id = $id;
        $doctor_id = $this->docSection->id;
        $this->dispatch('docAndSection', section: $section_id, doctor: $doctor_id);
        $this->dispatch('closeModal', true);
    }

    public function searchDocAndSection()
    {
        if ($this->step == 2) {
            $this->fetchData['sections'] =  $this->docSection->service()->where('title', 'LIKE', "%{$this->search}%")->take(10)->get();
        }
        $this->render();
    }
    //close modal button
    public function ignoreSearch()
    {
        $this->search = null;
        if ($this->step == 2) {
            $this->fetchData['sections'] =  $this->docSection->service->take(10);
        }
        $this->render();
    }
    public function render()
    {
        if ($this->step == 1) {
            $query = User::doctors_query()->when(isset($this->search) && !empty($this->search), function ($query) {
                return $query->whereHas('metas', function ($q) {
                    $q->where([
                        ['meta_key', UserMetaEnum::LAST_NAME],
                        ['meta_value', 'LIKE', "%{$this->search}%"],
                    ]);
                });
            })->take(10)
                ->get();
        } else {
            $query = User::doctors()->take(10);
        }

        return view(
            'appointmentsetting::livewire.modal.service-and-doctor-modal',
            ['doctors' =>  $query]
        );
    }
}
