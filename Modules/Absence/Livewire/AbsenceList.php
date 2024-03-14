<?php

namespace Modules\Absence\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class AbsenceList extends Component
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
        return view('absence::livewire.absence-list');
    }
}
