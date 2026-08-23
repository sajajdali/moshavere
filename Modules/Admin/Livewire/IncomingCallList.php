<?php

namespace Modules\Admin\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Admin\Exports\IncomingCallExport;
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
            filter_var(setting(SettingKeyEnum::VOIP_APPOINTMENT_STATUS), FILTER_VALIDATE_BOOLEAN),
            403,
        );
    }

    public function updatedIncoming(): void
    {
        $this->resetPage();
    }

    public function ExportData()
    {
        return Excel::download(
            new IncomingCallExport($this->incomingCallQuery()->get()),
            'incoming_calls.xlsx',
        );
    }

    public function render()
    {
        return view('admin::livewire.incoming-call-list', [
            'incomingCalls' => $this->incomingCallQuery()->paginate(20),
        ]);
    }

    private function incomingCallQuery()
    {
        return VoipIncoming::query()
            ->when($this->incoming !== '', fn ($query) => $query->where('incoming', 'like', '%'.$this->incoming.'%'))
            ->latest('id');
    }
}
