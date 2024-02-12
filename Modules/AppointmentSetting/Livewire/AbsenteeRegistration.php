<?php

namespace Modules\AppointmentSetting\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;

class AbsenteeRegistration extends Component
{
    public $step = 1;


    #[Url]
    public $search = [];

    //property for showing time sections
    public $counter = [
        'number' => 1,
    ];

    //store dates here
    public array $dates = [];
    public array $absentee = [];

    //list of the all doctors
    public $doctors;
    public $searchPanel = '';

    //selected doctors
    public array $doctor = [];

    public array $selectedDoctorsSection;
    public array $selectedSection = [];

    public function addCounter($obj)
    {
        $this->counter[$obj] = $this->counter[$obj] + 1;
        $this->render();
        $this->addjsclasses();
    }
    public function removeCounter($obj)
    {
        $this->counter[$obj] = $this->counter[$obj] - 1;
        $this->render();
        $this->addjsclasses();
    }
    public function AddStep()
    {
        $this->step =  $this->step + 1;
        $this->dispatch('jsloader', true);
    }
    public function prevStep()
    {
        $this->step =  $this->step - 1;
        $this->dispatch('loadjs',true);
    }
    public function addjsclasses()
    {
        $this->dispatch('jsloader', true);
    }
    public function lunchconfirmModal()
    {


        //TODO :: assign sections that relate to selected dorctors to this peroperty
        $this->selectedDoctorsSection = [
            0 => ['id' => 1, 'title' => 'ویزیت'],
            1 => ['id' => 2, 'title' => 'جراحی'],
        ];

        // lunch modal
        $this->dispatch('lunchSelectSectionMdal', true);
    }

    public function storeForAllSection()
    {
        //store setting for all sections
    }
    public function storeForSelectedsections()
    {
        //store setting for selected sections
    }

    public function searchDoctor()
    {
        $query = Role::find(3)->users();
        $this->doctors = $query->when(isset($this->search['id']) && !empty($this->search['id']), function ($query) {
            return $query->where('id', 'LIKE', "%{$this->search['id']}%");
        })->when(isset($this->search['mobile']) && !empty($this->search['mobile']), function ($query) {
            return $query->where('mobile', 'LIKE', "%{$this->search['mobile']}%");
        })->when(isset($this->search['first_name']) && !empty($this->search['first_name']), function ($query) {
            return $query->whereHas('metas', function ($q) {
                $q->where([
                    ['meta_key', UserMetaEnum::FIRST_NAME],
                    ['meta_value', 'LIKE', "%{$this->search['first_name']}%"],
                ]);
            });
        })->when(isset($this->search['last_name']) && !empty($this->search['last_name']), function ($query) {
            return $query->whereHas('metas', function ($q) {
                $q->where([
                    ['meta_key', UserMetaEnum::LAST_NAME],
                    ['meta_value', 'LIKE', "%{$this->search['last_name']}%"],
                ]);
            });
        })->get();
    }
    public function resetProperties()
    {
        $this->doctors = Role::find(3)->users;
        $this->search = [];
    }
    public function mount()
    {
        $this->doctors = Role::find(3)->users;
    }
    public function render()
    {
        return view('appointmentsetting::livewire.absentee-registration');
    }
}
