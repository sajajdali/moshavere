<?php

namespace Modules\Front\Livewire\HomePage;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Service\app\Models\Service;

#[Layout('front::layouts.app')]
class HomePageLivewire extends Component
{

    #[Locked]
    public array $fetchData = [];

    public function mount()
    {
        $this->fetchData['service'] = Service::mostViewedService();

        // Fetch doctors with dr_info_status set to true and order them by dr_info_order
        $this->fetchData['EmergencyDoctors'] = User::emergencyDoctors();
    }
    public function render()
    {
        return view('front::livewire.home-page.home-page-livewire');
    }
}
