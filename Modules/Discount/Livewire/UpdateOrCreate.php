<?php

namespace Modules\Discount\Livewire;

use Livewire\Component;
use Modules\User\Entities\User;
use Modules\Service\app\Models\Service;

class UpdateOrCreate extends Component
{
    public array $form = [
    ];
    public array $fetchData = [];
    public bool $isEdited = false;
    public function sotrediscount()
    {
        dd($this->form);
    }
    public function mount()
    {
        $this->fetchData['services'] = Service::all();
        $this->fetchData['doctors'] = User::doctors();
    }
    public function render()
    {
        return view('discount::livewire.update-or-create');
    }
}
