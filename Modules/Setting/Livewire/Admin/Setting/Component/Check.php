<?php

namespace Modules\Setting\Livewire\Admin\Setting\Component;

use Livewire\Component;
use Modules\Setting\Enum\SettingKeyEnum;

class Check extends Component
{
    public mixed $checkboxvalue = null;

    public mixed $old_value;

    public SettingKeyEnum $meta;

    public function mount()
    {
        if ($this->old_value !== null) {
            $this->checkboxvalue = $this->old_value;
        }
    }

    public function Checkboxvalue()
    {
        if ($this->meta === SettingKeyEnum::DISABLE_ONLINE_APPOINTMENT && auth()->id() !== 1) {
            return;
        }

        $this->dispatch('settingUpdateListener', settingKey: $this->meta->value, value: $this->checkboxvalue);
    }

    public function render()
    {
        return view('setting::livewire.admin.setting.component.check');
    }
}
