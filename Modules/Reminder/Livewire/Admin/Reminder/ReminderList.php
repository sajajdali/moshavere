<?php

namespace Modules\Reminder\Livewire\Admin\Reminder;

use App\EnumActiveEnum;
use Livewire\Component;
use App\Enum\ActiveEnum;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Modules\Reminder\app\Models\Reminder;
use Modules\Reminder\Enum\ReminderStatusEnum;
use Modules\Reminder\app\Models\AppointmentReminder;

class ReminderList extends Component
{
    #[Url]
    public $search = [];
    public $searchPanel = '';
    public function startSearch()
    {
        $this->render();
    }

    public function resetProperties()
    {
        $this->search = [];
        $this->searchPanel = '';
        $this->dispatch('closeCollaps', true);
        $this->render();
    }
    #[On('delete')]
    public function delete(Reminder $model)
    {
        AppointmentReminder::where('reminder_id', $model->id)->delete();
        $model->delete();
        return redirect()->route('admin.reminder.list')->with('success', 'تخصص با موفقیت حذف شد.');
    }
    public function render()
    {
        $Reminders = Reminder::when(isset($this->search['id']) && (int) $this->search['id'] !== 0, function ($query) {
            return $query->where('id', 'LIKE', '%' . $this->search['id'] . '%');
        })->when(isset($this->search['sendtype']), function ($query) {
            $Reminderid = Reminder::where('status',ReminderStatusEnum::tryFrom($this->search['sendtype']));
        })->when(isset($this->search['status']), function ($query) {
            return $query->filterStatus(ActiveEnum::tryFrom($this->search['status']));
        })->orderByDesc('id')->paginate(20);
        return view('reminder::livewire.admin.reminder.reminder-list', [
            'Reminders' => $Reminders
        ]);
    }

}
