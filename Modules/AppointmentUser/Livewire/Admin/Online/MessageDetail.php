<?php

namespace Modules\AppointmentUser\Livewire\Admin\Online;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Modules\AppointmentUser\app\Models\AppointmentOnline;

class MessageDetail extends Component
{
    #[Url]
    public $search;
    public array $form = [];
    public array $fetchData = [];
    public function runSearch()
    {
        $this->getMessages();
    }
    public function ignoreSearch() {
        unset($this->search);
        $this->fetchData['messages'] = $this->fetchData['appOnline']->messages;
        $this->getMessages();
    }
    #[Computed]
    public function getMessages()
    {
        if (isset($this->search)) {
            $this->fetchData['messages'] =  $this->fetchData['appOnline']->messages()->where('body', 'LIKE', "%{$this->search}%")->get();
        }
        if (isset($this->fetchData['messages']) && !empty($this->fetchData['messages'])) {
            $temp = $this->fetchData['messages']->groupBy(function ($messages) {
                return verta($messages->created_at)->format('%B %d، %Y');
            });
        }
        return $temp;
    }
    public function mount()
    {
        $this->fetchData['appOnline'] = AppointmentOnline::find(request()->route('onlineAppId'));
        $this->fetchData['messages'] = $this->fetchData['appOnline']->messages;
        $this->fetchData['user'] =  $this->fetchData['appOnline']->user;
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.online.message-detail');
    }
}
