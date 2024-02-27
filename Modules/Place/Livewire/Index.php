<?php

namespace Modules\Place\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class Index extends Component
{
    public $search = [];
    public $searchPanel ="";

    public function resetProperties() {
        $this->searchPanel = null ;
    }

    #[On('delete')]
    public function deletePlace() {
        return redirect()->route('admin.place.list')->with('success','مطب با موفقیت حذف شد');
    }
    public function render()
    {
        return view('place::livewire.index');
    }
}
