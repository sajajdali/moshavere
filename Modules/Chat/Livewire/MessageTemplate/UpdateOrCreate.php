<?php

namespace Modules\Chat\Livewire\MessageTemplate;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\Chat\App\Models\MessageTemplate;

class UpdateOrCreate extends Component
{
    use WithPagination;

    public array $form = [
        'active' => true,
    ];
    public array $fetchData = [];
    public bool $isEdited = false;
    public ?MessageTemplate $MessageTemplate;
    public ?string $msg = null;

    #[Url]
    public array $search =  [];

    public function createOrUpdateMessageTemplate()
    {
        $this->validate([
            'form.title'    => 'required|string|max:5000',
            'form.body'     => 'required|string|max:5000',
        ]);

        $model = [
            'title'       => $this->form['title'],
            'body'        => $this->form['body'],
            'priority'    => $this->form['priority'],
            'active'      => $this->form['active'] == true ? ActiveEnum::ACTIVE : ActiveEnum::DEACTIVE,
        ];
        if ($this->isEdited) {
            $this->MessageTemplate->update($model);
            $this->msg = 'با موفقیت ویرایش شد';
        } else {
            MessageTemplate::create($model);
            $this->msg = 'با موفقیت افزوده شد';
        }
        $this->isEdited = false;
        $this->form = [
            'active' => true,
            'priority' =>  MessageTemplate::maxPriority(),
        ];
    }
    public function editTemp(MessageTemplate $MessageTemplate)
    {
        $this->isEdited = true;
        $this->MessageTemplate  = $MessageTemplate;
        $this->form['title']    = $MessageTemplate->title;
        $this->form['body']     = $MessageTemplate->body;
        $this->form['priority'] = $MessageTemplate->priority;
        $this->form['active']   = $MessageTemplate->active == ActiveEnum::ACTIVE ? true : false;

        $this->dispatch('editMode', true);
    }
    public function mount()
    {
        $this->form['priority'] = MessageTemplate::maxPriority();
    }
    public function startSearch()
    {
        return $this->render();
    }
    public function resetProperties()
    {
        $this->search = [];
    }
    public function ignoreSearch()
    {
        $this->isEdited = false;
        $this->form = [
            'active' => true,
        ];
    }
    public function render()
    {
        $tempMessages = MessageTemplate::query()
            ->when(isset($this->search['id']), function ($q) {
                return $q->where('id', $this->search['id']);
            })->when(isset($this->search['title']), function ($q) {
                return $q->where('title', 'LIKE', "%{$this->search['title']}%");
            })->when(isset($this->search['active']), function ($q) {
                return $q->where('active', $this->search['active']);
            })
            ->paginate(50);
        return view('chat::livewire.message-template.update-or-create', ['tempMessages' => $tempMessages]);
    }
}
