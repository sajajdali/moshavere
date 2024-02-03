<?php

namespace Modules\Speciality\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Modules\Speciality\app\Models\Speciality;
use Modules\Speciality\Enum\SpecialityStatusEnum;

class SpecialityList extends Component
{
    #[Url]
    public $search = [];
    public $searchPanel = '';

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
    #[On('delete')]
    public function delete(Speciality $model)
    {
        $model->delete();
        return redirect()->route('admin.speciality.index')->with('success', 'تخصص با موفقیت حذف شد.');
    }
    public function render()
    {
        $specialities = Speciality::when(isset($this->search['id']) && (int) $this->search['id'] !== 0, function ($query) {
            return $query->where('id', 'LIKE', '%' . $this->search['id'] . '%');
        })->when(isset($this->search['specialityName']), function ($query) {
            $Specialityid = Speciality::where('title', 'LIKE', '%' . $this->search['specialityName'] . '%')->first()?->id ?? 0;
            return $query->where('id', $Specialityid);
        })->when(isset($this->search['status']), function ($query) {
            return $query->filterStatus(SpecialityStatusEnum::tryFrom($this->search['status']));
        })->orderBy('priority','asc')->paginate(20);
        return view('speciality::livewire.speciality-list', [
            'specialities' => $specialities
        ]);
    }
}
