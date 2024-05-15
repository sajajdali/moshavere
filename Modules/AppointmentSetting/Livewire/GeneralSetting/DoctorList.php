<?php

namespace Modules\AppointmentSetting\Livewire\GeneralSetting;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
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
        $this->render();
    }
    public function resetProperties()
    {
        $this->search = [];
        $this->searchPanel = '';
        $this->dispatch('closeCollaps', true);
        $this->render();
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
        $doctorsQuery = User::doctors_query();
        if(isset($doctorsQuery)) {
            $doctorsQuery->when(isset($this->search['id']) && (int) $this->search['id'] !== 0, function ($query) {
                return $query->where('id', $this->search['id']);
            })
            ->when(isset($this->search['mobile']) && !empty($this->search['mobile']), function ($query) {
                return $query->where('mobile', 'LIKE', "%{$this->search['mobile']}%");
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
            ->orderByDesc('id')->paginate(10);
        }

        return view('appointmentsetting::livewire.general-setting.doctor-list', [
            'doctors' => $doctorsQuery
        ]);
    }
}
