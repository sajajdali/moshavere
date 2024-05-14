<?php

namespace Modules\Admin\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Modules\User\Entities\User;
use Modules\Setting\Entities\Setting;
use Modules\Absence\app\Models\Absence;
use Modules\Setting\Policies\SettingPolicy;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;

#[Title('پیشخوان مدیریت')]
class Dashboard extends Component
{
    use WithPagination;

    public array $fetchData = [];
    public function mount()
    {
        // dd(auth()->user()->hasPermissionTo('absence'));
        // dd(auth()->user()->can('viewAny',AppointmentSegment::class));
        //scope functions can be found  in the models
        $this->fetchData['today_appointment']    = AppointmentUser::today()->successful()->get()?->count();
        $this->fetchData['pendding_appointment'] = AppointmentUser::waitpayment()->get()?->count();
        $this->fetchData['today_canceld_appointment'] = AppointmentUser::disabled()->today()->get()?->count();
        $this->fetchData['chart']['month'] = [verta()->format('F'), verta()->submonths(1)->format('F'), verta()->submonths(2)->format('F'), verta()->submonths(3)->format('F')];
        $this->fetchData['chart']['data']['successful'] = [
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(1)->toCarbon(), verta()->toCarbon()])->count(),
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(2)->toCarbon(), verta()->submonths(1)->toCarbon()])->count(),
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(3)->toCarbon(), verta()->submonths(2)->toCarbon()])->count(),
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(4)->toCarbon(), verta()->submonths(3)->toCarbon()])->count(),
        ];
        $this->fetchData['chart']['data']['canceld'] = [
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(1)->toCarbon(), verta()->toCarbon()])->count(),
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(2)->toCarbon(), verta()->submonths(1)->toCarbon()])->count(),
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(3)->toCarbon(), verta()->submonths(2)->toCarbon()])->count(),
            AppointmentUser::where('status',AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(4)->toCarbon(), verta()->submonths(3)->toCarbon()])->count(),
        ];
    }
    public function render()
    {
        $today_app =  AppointmentUser::today()->paginate(10);
        return view('admin::livewire.dashboard', ['today_app' => $today_app]);
    }
}
