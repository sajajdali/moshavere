<?php

namespace Modules\Absence\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Absence\app\Models\Absence;

class AbsenceList extends Component
{
    use WithPagination;
    public $searchPanel = "";

    #[Url]
    public array $search = [];
    public $absentees;

    #[On('delete')]
    public function delete($model)
    {
        $absence  = Absence::find($model);
        $absence->delete();
    }

    public function startSearch()
    {
        return $this->render();
    }
    public function resetProperties()
    {
        $this->search = [];
        $this->dispatch('closeCollaps', true);
        return $this->render();
    }
    public function render()
    {
        $query = Absence::when(isset($this->search['id']) && !empty($this->search['id']), function ($q) {
            return $q->where('id', $this->search['id']);
        })->when(isset($this->search['doc_name']) && !empty($this->search['doc_name']), function ($q) {
            return $q->whereHas('user', function ($qq) {
                $qq->whereHas('metas', function ($qqq) {
                    $qqq->where([
                        ['meta_key', UserMetaEnum::LAST_NAME],
                        ['meta_value', 'LIKE', "%{$this->search['doc_name']}%"],
                    ]);
                });
            });
        })->when(isset($this->search['service_name']) && !empty($this->search['service_name']), function ($q) {
            return $q->whereHas('service',function($qq) {
                $qq->where('title','LIKE',"%{$this->search['service_name']}%");
            });
        })->when(isset($this->search['start_date']) && !empty($this->search['start_date']), function ($q) {
            return $q->where('start_at', Verta::parse($this->search['start_date'])->toCarbon());
        })->when(isset($this->search['end_date']) && !empty($this->search['end_date']), function ($q) {
            return $q->where('end_at', Verta::parse($this->search['end_date'])->toCarbon());
        });
        return view('absence::livewire.absence-list', [
            'absences' => $query->paginate(10)
        ]);
    }
}
