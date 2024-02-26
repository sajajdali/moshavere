<?php

namespace Modules\Service\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class CreateOrUpdate extends Component
{
    public $doctors;
    public array $doctor = [];
    public $serviceImg ;


    public function createSection() {
        return redirect()->route('admin.service.list')->with('success','بخش با موفقیت اضافه شد');
    }
    public function mount() {

        $seciality = request()->route('speciality');
        $this->doctors = Role::find(3)->users;
    }
    public function render()
    {
        return view('service::livewire.create-or-update');
    }
}
