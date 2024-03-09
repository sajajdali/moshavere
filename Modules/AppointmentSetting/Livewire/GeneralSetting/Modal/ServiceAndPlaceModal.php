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
    public array $fetchData = [];
    public array $form = [];
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
        $this->form['place'] = $placeId;
        $this->step = $this->step + 1;
    }

    public function selectSection($serviceId)
    {
        $user       = $this->doctor->id;
        $service    =  $serviceId;
        $place      =  $this->form['place'];
        session()->flash('resetTheSetting', true);
        return redirect()->route('admin.appointment.setting.specialservice', [$user, $service, $place]);
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
    public function handelSearch()
    {
        if ($this->step == 1) {
            $query = Place::when(isset($this->search) && !empty($this->search), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search}%");
            })->take(10)->get();
        } else {
            $query = $this->doctor->service()->when(isset($this->search) && !empty($this->search), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search}%");
            })->get()->take(10);
        }
        return $query;
    }
    public function render()
    {
        //handel search
        $retunrValue = $this->handelSearch();

        return view(
            'appointmentsetting::livewire.general-setting.modal.service-and-place-modal',
            ['ServiceOrPlace' =>  $retunrValue]
        );
    }
}
