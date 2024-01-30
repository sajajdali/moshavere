<?php

namespace Modules\Admin\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Hekmatinasser\Verta\Verta;
use Livewire\Attributes\Title;
use Modules\User\Entities\User;
use Modules\Diet\Entities\DietRequest;
use Modules\Diet\Enum\DietRequestStatusEnum;
use Modules\Transaction\Entities\Transaction;
use Modules\Transaction\Enum\TransactionStatusEnum;

#[title('پیشخوان مدیریت')]
class Dashboard extends Component
{
  
    public function render()
    {
        return view('admin::livewire.dashboard');
    }
}
