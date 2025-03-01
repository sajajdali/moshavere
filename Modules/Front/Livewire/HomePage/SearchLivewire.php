<?php

namespace Modules\Front\Livewire\HomePage;

use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Modules\Front\Traits\SearchPage;
use Modules\Service\app\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Modules\Speciality\app\Models\Speciality;

#[Layout('front::layouts.app')]
#[Title('جست و جو')]
class SearchLivewire extends Component
{
    use SearchPage;
    #[Locked]
    public array $fetchData = [];
    #[Locked]
    public array $filter = [];
    public array $form = [];

    #[Url]
    public $query;

    // ----------------------------------------------------------------------------------
    // all the required methods can b find in the Modules\Front\Traits\SearchPage trait
    // ----------------------------------------------------------------------------------

    public function mount()
    {
        $this->query = request()->get('query');
        if (request()->has('service_id')) {
            $this->fetchData['service_id'] =  htmlspecialchars(request()->input('service_id'), ENT_QUOTES, 'UTF-8');
            $this->fetchData['settApp']['service'] =  $this->fetchData['service_id'];
        }
        if (request()->has('province')) {
            $this->fetchData['province_id'] =  htmlspecialchars(request()->input('province'), ENT_QUOTES, 'UTF-8');
        }
        $this->fillTheFilters();
        $this->searchIn();
    }
    public function render()
    {
        return view('front::livewire.home-page.search-livewire');
    }
}
