<?php

namespace Modules\Admin\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Api\app\Models\VoipVoiceRecord;
use Modules\Setting\Enum\SettingKeyEnum;

#[Title('پیغام های ضبط شده')]
class VoiceRecordList extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        abort_unless(
            auth()->id() === 1
            && filter_var(setting(SettingKeyEnum::VOIP_VOICE_RECORD_STATUS), FILTER_VALIDATE_BOOLEAN),
            403,
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('admin::livewire.voice-record-list', [
            'voiceRecords' => VoipVoiceRecord::query()
                ->with('user')
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($query) {
                        $query->where('incoming', 'like', '%' . $this->search . '%')
                            ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
                })
                ->latest('id')
                ->paginate(20),
        ]);
    }
}
