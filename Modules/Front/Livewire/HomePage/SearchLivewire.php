<?php

namespace Modules\Front\Livewire\HomePage;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Modules\User\Entities\User;
use Livewire\Attributes\Computed;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Modules\Service\app\Models\Service;
use Modules\Speciality\app\Models\Speciality;

#[Layout('front::layouts.app')]
#[Title('جست و جو')]
class SearchLivewire extends Component
{
    #[Locked]
    public array $fetchData = [];
    #[Locked]
    public array $filter = [];
    public array $form = [];

    #[Url]
    public $query;

    public function messages()
    {
        return [
            'query.required' => 'متن جست و جو را وارد کنید!',
            'query.string' => 'فرمت وارد شده صحیح نیست!',
            'query.max' => 'متن وارد شده از حداکثر کاراکتر مجاز بیشتر است!',
        ];
    }
    public function RenewSearch()
    {
        // $this->validate([
        //     'query' => 'required|string|max:225'
        // ]);
        $this->render();
    }
    #[Computed]
    public function searchIn(): array
    {
        $sanitizedInput = htmlspecialchars($this->query, ENT_QUOTES, 'UTF-8');

        // Initialize results array
        $result = [];

        // Places query
        $places = Place::where('title', 'LIKE', '%' . $sanitizedInput . '%')->get();
        if ($places->isNotEmpty()) {
            $result['place'] =  $places;
        }

        // Doctors query
        $doctors = User::doctors_query()->when(isset($this->filter['speciality']), function ($query) {
            $query->whereHas('specialities', function ($qq) {
                $qq->where('title', 'LIKE', "%{$this->filter['speciality']}%");
            });
        })->whereHas('metas', function ($q) use ($sanitizedInput) {
            $q->where(function ($q) use ($sanitizedInput) {
                $q->where([
                    ['meta_key', UserMetaEnum::FIRST_NAME],
                    ['meta_value', 'LIKE', "%{$sanitizedInput}%"]
                ])->orWhere([
                    ['meta_key', UserMetaEnum::LAST_NAME],
                    ['meta_value', 'LIKE', "%{$sanitizedInput}%"]
                ]);
            });
        })->get();
        if ($doctors->isNotEmpty()) {
            $result['doctors'] =  $doctors;
        }

        // Services query
        $services = Service::where('title', 'LIKE', "%{$sanitizedInput}%")->get();
        if ($services->isNotEmpty()) {
            $result['service'] = $services;
        }
        //apply filters 
        if (isset($this->filter['speciality'])) {
            if (isset($result['doctors'])) {
                $result = array_filter($result, function ($key) {
                    return $key === 'doctors';
                }, ARRAY_FILTER_USE_KEY);
                return $result;
            }
            return [];
        }
        //apply filters 
        if (isset($this->filter['service'])) {
            if (isset($result['service'])) {
                $result = array_filter($result, function ($key) {
                    return $key === 'service';
                }, ARRAY_FILTER_USE_KEY);
                return $result;
            }
            return [];
        }

        return $result;
    }

    private function fillTheFilters()
    {
        $this->fetchData['specilities'] =  Speciality::all();
        $this->fetchData['services']    =  Service::all();
    }

    public function applyFilter($name, $category)
    {
        $this->filter[$category] = $name;
    }
    public function removeFilter($item)
    {
        if ($item === 'all') {
            $this->dispatch('removeFilterAll', true);
            return $this->filter = [];
        }

        if (!empty($this->filter) &&  in_array($item, $this->filter)) {
            $index = array_search($item, $this->filter);
            $this->dispatch('removeFilter', $index);
            unset($this->filter[$index]);
            $this->render();
        }
    }
    public function mount()
    {
        $this->query = request()->get('query');
        $this->fillTheFilters();
    }
    public function render()
    {
        $this->searchIn();
        return view('front::livewire.home-page.search-livewire');
    }
}
