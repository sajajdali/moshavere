<?php

namespace Modules\AppointmentSetting\Livewire\Absentee;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class AbsenteeList extends Component
{
    public $searchPanel = "";

    #[Url]
    public array $search =[];
    public $absentees ;

    #[On('delete')]
    public function delete($model)
    {
        //run delete command
    }
    public function render()
    {
        return view('appointmentsetting::livewire.absentee.absentee-list');
    }
}
