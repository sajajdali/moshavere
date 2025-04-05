<?php

namespace Modules\AppointmentSetting\Livewire\Segment;

use Google\ApiCore\ResourceTemplate\Segment;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;

#[title('مدیریت بخش بندی ها')]
class SegmentList extends Component
{
    use withPagination;

    #[Url]
    public $search = [];
    public $searchPanel = '';

    #[On('delete')]
    public function delete(Segment $model)
    {
        $this->authorize('delete', $model);

        try {
            $model->delete();
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.segment.index')->with('success', 'بخش بندی با موفقیت حذف شد.');
    }

    public function startSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (request()->has('search')){
            $this->searchPanel = 'show';
        }
        $segments = AppointmentSegment::when(isset($this->search['id']) && (int) $this->search['id'] !== 0, function ($query) {
            return $query->where('id', $this->search['id']);
        })
            ->when(isset($this->search['title']) && ! empty($this->search['title']), function ($query) {
                return $query->where('title', 'LIKE', "%{$this->search['title']}%");
            })
            ->orderByDesc('created_at')
            ->paginate(50);
        return view('appointmentsetting::livewire.segment.segment-list' , compact('segments'));
    }
}
