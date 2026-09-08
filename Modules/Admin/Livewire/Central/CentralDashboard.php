<?php

namespace Modules\Admin\Livewire\Central;

use App\Models\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\User\Entities\User;

class CentralDashboard extends Component
{
    #[Layout('admin::layouts.central.app')]
    public function render()
    {
        $tenants = Tenant::query()->with('domains')->latest('created_at')->get();
        $customersById = User::query()
            ->whereIn('id', $tenants->pluck('customer_id')->filter()->unique())
            ->get()->keyBy('id');

        return view('admin::livewire.central.central-dashboard', [
            'tenants' => $tenants,
            'activeCount' => $tenants->where('disabled', false)->count(),
            'customersById' => $customersById,
        ]);
    }
}
