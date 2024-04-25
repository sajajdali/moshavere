<?php

namespace Modules\Admin\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

#[Title('پیشخوان مدیریت')]
class Dashboard extends Component
{
    use WithPagination ;
    public array $fetchData = [];
    public function mount()
    {
        //scope functions can be found  in the models
        $this->fetchData['today_appointment']    = AppointmentUser::today()->successful()->get()?->count();
        $this->fetchData['pendding_appointment'] = AppointmentUser::waitpayment()->get()?->count();
        $this->fetchData['today_canceld_appointment'] = AppointmentUser::disabled()->today()->get()?->count();
    }
    public function render()
    {
        $today_app =  AppointmentUser::today()->paginate(10);
        return view('admin::livewire.dashboard',['today_app'=>$today_app]);
    }
}
