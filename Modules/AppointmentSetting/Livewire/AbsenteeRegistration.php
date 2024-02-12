<?php

namespace Modules\AppointmentSetting\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Spatie\Permission\Models\Role;

class AbsenteeRegistration extends Component
{
    #[Url]
    public $search = [];
    public $counter = [
        'number' => 1,
    ];
    public array $dates = [];
    public array $absentee = [];
    public $doctors;
    public array $doctor = [];

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
    public function addjsclasses()
    {
        $this->dispatch('jsloader', true);
    }
    public function storeDay()
    {
        dd($this->dates);
    }
    public $searchPanel = '';

    public function mount()
    {
        $this->doctors = Role::find(3)->users;
    }
    public function render()
    {
        return view('appointmentsetting::livewire.absentee-registration');
    }
}
