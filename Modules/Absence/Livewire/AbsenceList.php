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
        return redirect()->route('admin.absence.list')->with('success', 'عدم حضور با موفقیت حذف شد');
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
    public function mount()
    {
        Absence::where('end_at', '<', now()->subDay())->delete();
    }
    public function render()
    {
        $permistion_check = auth()->user();
        if (
            !$permistion_check->can('absence', Absence::class)
            && !$permistion_check->can('absence.own', Absence::class)
        ) {
            abort(403, 'Unauthorized');
        }
        $query = Absence::when(isset($this->search['id']) && !empty($this->search['id']), function ($q) {
            return $q->where('id', $this->search['id']);
        })->when(! $permistion_check->isAdmin() && $permistion_check->can('absence.own'), function ($q) use ($permistion_check) {
            return $q->whereHas('user', function ($qq) use ($permistion_check) {
                return $qq->where('id', $permistion_check->id);
            });
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
            return $q->whereHas('service', function ($qq) {
                $qq->where('title', 'LIKE', "%{$this->search['service_name']}%");
            });
        })->when(isset($this->search['start_date']) && !empty($this->search['start_date']), function ($q) {
            try {
                $d = Verta::parse($this->search['start_date'])->toCarbon();
            } catch (\Throwable $th) {
                $this->dispatch('error', message: 'تاریخ انتخابی صحیح نیست');
                unset($this->search['start_date']);
                return;
            }
            return $q->where('start_at', '>=', $d);
        })->when(! is_null(data_get($this->search,'end_date',null) ), function ($q) {
            try {
                $endDate = Verta::parse($this->search['end_date'])->toCarbon();
            } catch (\Throwable $th) {
                $this->dispatch('error', message: 'تاریخ انتخابی صحیح نیست');
                unset($this->search['end_date']);
                return;
            }
            return $q->where('end_at', '<=', $endDate);
        })->orderBy('end_at');
        return view('absence::livewire.absence-list', [
            'absences' => $query->paginate(20)
        ]);
    }
}
