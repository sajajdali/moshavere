<?php

namespace Modules\Service\Livewire;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Modules\User\Entities\User;
use Spatie\Permission\Models\Role;
use Modules\Service\app\Models\Service;

class CreateOrUpdate extends Component
{
    public ?Service $service;
    public $isEdited = false;
    public array $form = [
        'parent_id' => null,
        'doctors' => []
    ];
    public array $fetchdata = [];

    public function rules()
    {
        return [
            'form.title' => 'required|string|max:225',
            'form.parentId' => 'nullable|integer',
            'form.priority' => 'required|integer',
            'form.img' => 'nullable',
            'form.active' => 'nullable',
        ];
    }

    public function createOrUpdateSection()
    {
        $this->validate();

        //data for update Or create Service
        $parentId = $this->form['parent_id'] == 0 || null ? null : $this->form['parent_id'];
        $active = $this->form['active'] == 'true' ? 1 : 0;
        $modelCreateOrUpdate = [
            'title'         => $this->form['title']         ?? '',
            'parent_id'     => $parentId,
            'icon'          => $this->form['img']            ?? null,
            'priority'      => $this->form['priority']   ?? 1,
            'active'        => ActiveEnum::tryFrom($active),
        ];
        if ($this->isEdited) {
            $this->service->update($modelCreateOrUpdate);
        } else {
            $this->service =  Service::create($modelCreateOrUpdate);
        }
        //add doctors to Service
        if (isset($this->form['doctors'])) {
            $syncArr = [];
            foreach ($this->form['doctors'] as $userId => $value) {
                //check if check box checked
                if ($value) {
                    $syncArr[] = $userId;
                }
            }
            $this->service->user()->sync($syncArr);
        }
        return redirect()->route('admin.service.list')->with('success', 'بخش با موفقیت اضافه شد');
    }

    private function addInitialValues()
    {
        $this->form['title']     = $this->service->title;
        $this->form['parent_id'] = $this->service->parent_id;
        $this->form['img']       = $this->service->icon;
        $this->form['priority']  = $this->service->priority;
        $this->form['active']    =  $this->service->active == ActiveEnum::ACTIVE ? 'true' : 'false';
        $doctors =  $this->service->user->pluck('id')->toArray() ;
        foreach ($doctors as $doc) {
            $this->form['doctors'][$doc] =  true;
        }

    }
    public function mount()
    {
        $service = request()->route('service');
        if ($service instanceof Service) {
            $this->service           =  $service;
            $this->isEdited          = true;
            $this->addInitialValues();
        } else {
            $this->form['priority']      = Service::maxPriority();
            $this->form['active']        = 'true';
        }
        $this->fetchdata['doctors']  = User::doctors();
        $this->fetchdata['services'] = Service::all();
    }
    public function render()
    {
        return view('service::livewire.create-or-update');
    }
}
