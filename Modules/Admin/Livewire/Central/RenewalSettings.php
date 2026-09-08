<?php

namespace Modules\Admin\Livewire\Central;

use App\Models\CentralSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

class RenewalSettings extends Component
{
    public string $supportRenewCost = '0';
    public string $serverRenewCost = '0';

    public function mount(): void
    {
        $settings = CentralSetting::current();
        $this->supportRenewCost = (string) $settings->support_renew_cost;
        $this->serverRenewCost = (string) $settings->server_renew_cost;
    }

    public function save(): void
    {
        $data = $this->validate([
            'supportRenewCost' => ['required', 'integer', 'min:0'],
            'serverRenewCost' => ['required', 'integer', 'min:0'],
        ], [
            'required' => 'وارد کردن :attribute الزامی است.',
            'integer' => ':attribute باید عدد صحیح باشد.',
            'min' => ':attribute نمی‌تواند منفی باشد.',
        ], [
            'supportRenewCost' => 'هزینه تمدید پشتیبانی',
            'serverRenewCost' => 'هزینه تمدید سرور',
        ]);

        CentralSetting::current()->update([
            'support_renew_cost' => (int) $data['supportRenewCost'],
            'server_renew_cost' => (int) $data['serverRenewCost'],
        ]);

        session()->flash('success', 'هزینه‌های تمدید با موفقیت ذخیره شدند.');
    }

    #[Layout('admin::layouts.central.app')]
    public function render()
    {
        return view('admin::livewire.central.renewal-settings');
    }
}
