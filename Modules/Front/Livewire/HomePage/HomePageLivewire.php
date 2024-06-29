<?php

namespace Modules\Front\Livewire\HomePage;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Modules\Front\app\Models\Faq;
use Modules\User\Entities\User;
use Modules\Service\app\Models\Service;

#[Layout('front::layouts.app')]
#[Title('صفحه اصلی')]
class HomePageLivewire extends Component
{

    #[Locked]
    public array $fetchData = [];
    public array $form= [];

    public function searchFor() {
        if(isset($this->form['searchProp'])) {
            $sanitizedInput = htmlspecialchars($this->form['searchProp'], ENT_QUOTES, 'UTF-8');
            return redirect()->route('front.searchPage', ['query' => $sanitizedInput]);
        }

    }
    public function mount()
    {
        $this->fetchData['service'] = Service::mostViewedService();

        // Fetch doctors with dr_info_status set to true and order them by dr_info_order
        $this->fetchData['EmergencyDoctors']    = User::emergencyDoctors();
        $this->fetchData['introductionDoctors'] = User::introductionDoctors();
        $this->fetchData['introductionDoctors'] = User::NewestDocs();
        $this->fetchData['faqs'] = Faq::all();
    }
    public function render()
    {
        return view('front::livewire.home-page.home-page-livewire');
    }
}
