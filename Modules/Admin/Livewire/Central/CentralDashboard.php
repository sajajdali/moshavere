<?php

namespace Modules\Admin\Livewire\Central;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CentralDashboard extends Component
{
    public function mount()
    {
    }
    #[Layout('admin::layouts.central.app')]
    public function render()
    {

        return view('admin::livewire.central.central-dashboard');
    }
}
