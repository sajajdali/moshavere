<?php

namespace Modules\Service\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class Index extends Component
{
    public  $search = [];
    public $searchPanel = "";

    #[On('delete')]
    public function delete() {
        return redirect()->route('admin.service.list')->with('success','با موفقیت حذف شد');
    }

    public function render()
    {
        return view('service::livewire.index');
    }
}
