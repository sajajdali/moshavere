<?php

namespace Modules\Service\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Modules\Service\app\Models\Service;

class CreateOrUpdate extends Component
{
    public array $form = [];
    public array $fetchdata = [];
    public $doctors;
    public array $doctor = [];
    public $serviceImg;


    public function createSection()
    {
        return redirect()->route('admin.service.list')->with('success', 'بخش با موفقیت اضافه شد');
    }
    public function mount()
    {
        $service = request()->route('service');
        if ($service instanceof Service) {
            $service = request()->route('speciality');
        } else {
            $this->fetchdata['doctors'] = Role::find(3)->users;
            $this->fetchdata['services'] = Service::all();

        }
    }
    public function render()
    {
        return view('service::livewire.create-or-update');
    }
}
