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
            filter_var(setting(SettingKeyEnum::VOIP_VOICE_RECORD_STATUS), FILTER_VALIDATE_BOOLEAN),
            403,
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function markAsListened(int $voiceRecordId): void
    {
        VoipVoiceRecord::whereKey($voiceRecordId)
            ->whereNull('listened_at')
            ->update(['listened_at' => now()]);
    }

    public function delete(int $voiceRecordId): void
    {
        $voiceRecord = VoipVoiceRecord::find($voiceRecordId);

        if (! $voiceRecord) {
            return;
        }

        $absolutePath = public_path($voiceRecord->file_path);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }

        $voiceRecord->delete();
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
