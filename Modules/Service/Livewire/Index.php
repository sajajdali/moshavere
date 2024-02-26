<?php

namespace Modules\Service\Livewire;

use Livewire\Component;

class Index extends Component
{
    public  $search = [];
    public $searchPanel = "";
    public function render()
    {
        return view('service::livewire.index');
    }
}
