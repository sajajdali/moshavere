<?php

namespace Modules\Chat\Livewire\MessageTemplate;

use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Modules\Chat\app\Models\MessageTemplate;

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

    public function removeFiles()
    {
        if (isset($this->form['file'])) {
            unset($this->form['file']);
        }
        if (isset($this->form['voice'])) {
            unset($this->form['voice']);
        }
    }
    public function createOrUpdateMessageTemplate()
    {

        $this->validate([
            'form.title'    => 'required|string|max:5000',
            'form.body'     => 'nullable|string|max:5000',
            'form.voice'    => 'nullable|string',
            'form.file'     => 'nullable|string',
            'form.limitToDoctor'     => 'required',
        ]);
        $model = [
            'title'       => $this->form['title'],
            'priority'    => $this->form['priority'],
            'active'      => $this->form['active'] == true ? ActiveEnum::ACTIVE : ActiveEnum::DEACTIVE,
        ];
        if (isset($this->form['body'])) {
            $model['body'] = $this->form['body'];
        }
        $model['detail']['doc'] = $this->form['limitToDoctor'];
        if (isset($this->form['voice'])) {
            $model['detail']['voice'] = $this->form['voice'];
        } else {
            $model['detail']['voice'] = null;
        }
        if (isset($this->form['file'])) {
            $model['detail']['file'] = $this->form['file'];
        } else {
            $model['detail']['file'] = null;
        }
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
        $this->dispatch('editMode', true);
    }
    public function editTemp(MessageTemplate $MessageTemplate)
    {
        $this->isEdited = true;
        $this->MessageTemplate  = $MessageTemplate;
        $this->form['title']    = $MessageTemplate->title;
        $this->form['body']     = $MessageTemplate->body;
        $this->form['priority'] = $MessageTemplate->priority;
        $this->form['active']   = $MessageTemplate->active == ActiveEnum::ACTIVE ? true : false;
        if (isset($MessageTemplate['detail']['doc'])) {
            $this->form['limitToDoctor'] =     $MessageTemplate['detail']['doc'];
        }
        if (isset($MessageTemplate->detail)) {
            isset($MessageTemplate->detail['file']) ?  $this->form['file'] = $MessageTemplate->detail['file'] : '';
            isset($MessageTemplate->detail['voice']) ?  $this->form['voice'] = $MessageTemplate->detail['voice'] : '';
        }

        $this->dispatch('editMode', true);
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
    #[On('delete')]
    public function newMessage(MessageTemplate $model): void
    {
        $model->delete();
        $this->msg = 'متن با موفقیت حذف شد';
    }

    public function mount()
    {
        $this->form['priority'] = MessageTemplate::maxPriority();
        $this->fetchData['doctors'] = User::doctors();
    }
    public function boot()
    {
        return $this->dispatch('loadJs', true);
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
