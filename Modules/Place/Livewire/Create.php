<?php

namespace Modules\Place\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    public array $counter = [
        'number' => 1,
    ];
    public array $place = [];
    public $doctors;
    public array $doctor;
    public function addCounter($obj)
    {
        $this->counter[$obj] = $this->counter[$obj] + 1;
        $this->render();
    }
    public function removeCounter($obj)
    {
        $this->counter[$obj] = $this->counter[$obj] - 1;
        $this->render();
    }
    public function UpdateOrCreatePlace() {
        dd($this->place) ; 
    }
    public function mount()
    {
        $this->doctors = Role::find(3)->users;
    }
    public function render()
    {
        return view('place::livewire.create');
    }
}
