<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting\Modal;

use Livewire\Component;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;

class ServiceAndPlaceModal extends Component
{
    public $search;
    public $step;
    public $doctor, $placeId;
    public $fetchData;
    public function dismisMOdal()
    {
        $this->step = 1;
        $this->search = null;
    }

    public function searchDocAndSection()
    {
        if ($this->step == 2) {
            $this->fetchData['sections'] =  $this->docSection->service()->where('title', 'LIKE', "%{$this->search}%")->take(10)->get();
        }
        $this->render();
    }
    public function ignoreSearch()
    {
        $this->search = null;
        if ($this->step == 2) {
            $this->fetchData['sections'] =  $this->docSection->service->take(10);
        }
        $this->render();
    }
    public function showRelatedSection($placeId)
    {
        // TODO::
        // show related section to this place
    }

    public function selectSection($serviceId)
    {
        return redirect()->route('appointment.setting.specialservice', $this->doctor, $serviceId, $this->placeId);
    }
    public function mount()
    {
        $places =  Place::all();

        //check if there is multiple place
        if ($places->isNotEmpty() && $places->count() > 1) {
            $this->step = 1;
        } else {
            $this->placeId =  $places->first();
            $this->step = 2;
        }
    }
    public function render()
    {
        //handel search
        if ($this->step == 1) {
            $query = Place::when(isset($this->search) && !empty($this->search), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search}%");
            })->take(10)->get();
        } else {
            $query = $this->doctor->service()->when(isset($this->search) && !empty($this->search), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search}%");
            })->get()->take(10);
        }

        return view(
            'appointmentsetting::livewire.general-setting.modal.service-and-place-modal',
            ['ServiceOrPlace' =>  $query]
        );
    }
}
