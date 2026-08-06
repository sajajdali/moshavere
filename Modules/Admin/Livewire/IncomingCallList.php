<?php

namespace Modules\Admin\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Api\app\Models\VoipIncoming;
use Modules\Setting\Enum\SettingKeyEnum;

#[Title('تماس‌های ورودی')]
class IncomingCallList extends Component
{
    use WithPagination;

    public string $incoming = '';

    public function mount(): void
    {
        abort_unless(
            auth()->id() === 1
            && filter_var(setting(SettingKeyEnum::VOIP_APPOINTMENT_STATUS), FILTER_VALIDATE_BOOLEAN),
            403,
        );
    }

    public function updatedIncoming(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('admin::livewire.incoming-call-list', [
            'incomingCalls' => VoipIncoming::query()
                ->when($this->incoming !== '', fn ($query) => $query->where('incoming', 'like', '%'.$this->incoming.'%'))
                ->latest('id')
                ->paginate(20),
        ]);
    }
}
