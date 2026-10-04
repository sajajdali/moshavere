<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Modules\Place\app\Models\Place;
use Modules\User\Enum\UserMetaEnum;
use Modules\Service\app\Models\Service;

class DoctorList extends Component
{
    use WithPagination;
    public $isEdited;

    #[Url]
    public $search = [];
    public $searchPanel = '';
    public array $form  = [];

    public function startSearch()
    {
        $this->resetPage();
        $this->render();
    }
    public function resetProperties()
    {
        $this->resetPage();
        $this->search = [];
        $this->searchPanel = '';
        $this->dispatch('closeCollaps', true);
        $this->render();
    }
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function mount()
    {
        $serviec =  Service::exists();
        $place = Place::exists();
        if ($serviec) {
            $this->form['services'] = false;
        } else {
            $this->form['services'] = true;
        }
        if ($place) {
            $this->form['place'] = false;
        } else {
            $this->form['place'] = true;
        }

    }


    public function render()
    {
        $permission_check = auth()->user();
        $doctorsQuery = User::doctors_query();
        // one box searches first name, last name, mobile and id; Persian digits are accepted
        $term = trim(strtr((string) ($this->search['q'] ?? ''), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]));
        $nameKeys = [UserMetaEnum::FIRST_NAME->value, UserMetaEnum::LAST_NAME->value];
        if ($doctorsQuery?->exists()) {
            $doctorsQuery =  $doctorsQuery
                ->with(['metas', 'specialities'])
                ->withCount([
                    'service',
                    'appointmentSettings as special_settings_count' => fn ($query) => $query->whereNotNull('service_id'),
                ])
                ->withExists([
                    'appointmentSettings as has_general_setting' => fn ($query) => $query->whereNull('service_id'),
                ])
                ->when($term !== '', function ($query) use ($term, $nameKeys) {
                    // names live in user metas, so every word of a full name must match a first or last name meta
                    return $query->where(function ($query) use ($term, $nameKeys) {
                        $query->where(function ($query) use ($term, $nameKeys) {
                            foreach (preg_split('/\s+/u', $term) as $word) {
                                $query->whereHas('metas', fn ($meta) => $meta
                                    ->whereIn('meta_key', $nameKeys)
                                    ->where('meta_value', 'LIKE', "%{$word}%"));
                            }
                        })
                            ->orWhere('users.mobile', 'LIKE', "%{$term}%")
                            ->when(ctype_digit($term), fn ($query) => $query->orWhere('users.id', (int) $term));
                    });
                })
                ->when(isset($this->search['id']) && (int) $this->search['id'] !== 0, function ($query) {
                return $query->where('users.id', $this->search['id']);
            })->when(! $permission_check->isAdmin() && $permission_check->can('AppointmentSetting.own'), function ($query) use($permission_check) {
                    return $query->where('users.id',$permission_check->id);
                })->when(isset($this->search['mobile']) && !empty($this->search['mobile']), function ($query) {
                    return $query->where('users.mobile', 'LIKE', "%{$this->search['mobile']}%");
                })
                ->when(isset($this->search['first_name']) && !empty($this->search['first_name']), function ($query) {
                    return $query->whereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::FIRST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['first_name']}%"],
                        ]);
                    });
                })
                ->when(isset($this->search['last_name']) && !empty($this->search['last_name']), function ($query) {
                    return $query->whereHas('metas', function ($q) {
                        $q->where([
                            ['meta_key', UserMetaEnum::LAST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['last_name']}%"],
                        ]);
                    });
                })
                ->orderByDesc('users.id')->paginate(20);
        } else {
            $doctorsQuery = null;
        }

        return view('appointmentsetting::livewire.general-setting.doctor-list', [
            'doctors' => $doctorsQuery
        ]);
    }
}
