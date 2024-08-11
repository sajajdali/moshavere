<?php

namespace Modules\Admin\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Cache;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessage;
use Modules\Transaction\app\Models\Transaction;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

#[Title('پیشخوان مدیریت')]
class Dashboard extends Component
{
    use WithPagination;

    public array $fetchData = [];
    public function mount()
    {
        //scope functions can be found  in the models
        // dd(auth()->user());
        $this->fetchData['today_appointment']    = AppointmentUser::today()->successful()->get()?->count();
        $this->fetchData['pendding_appointment'] = AppointmentUser::waitpayment()->get()?->count();
        $this->fetchData['new_online_messages'] = AppointmentOnlineMessage::badgeCount();
        $this->fetchData['chart']['month'] = [verta()->format('F'), verta()->submonths(1)->format('F'), verta()->submonths(2)->format('F'), verta()->submonths(3)->format('F')];

        $cacheKeySuccessful = 'appointment_successful_counts';
        $cacheKeyCanceled = 'appointment_canceled_counts';
        $this->fetchData['chart']['data']['successful'] = Cache::remember($cacheKeySuccessful, now()->addDay(), function () {
            return [
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(1)->toCarbon(), verta()->toCarbon()])->count(),
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(2)->toCarbon(), verta()->submonths(1)->toCarbon()])->count(),
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(3)->toCarbon(), verta()->submonths(2)->toCarbon()])->count(),
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(4)->toCarbon(), verta()->submonths(3)->toCarbon()])->count(),
            ];
        });

        $this->fetchData['chart']['data']['canceld'] = Cache::remember($cacheKeyCanceled, now()->addDay(), function () {
            return [
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(1)->toCarbon(), verta()->toCarbon()])->count(),
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(2)->toCarbon(), verta()->submonths(1)->toCarbon()])->count(),
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(3)->toCarbon(), verta()->submonths(2)->toCarbon()])->count(),
                AppointmentUser::where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(4)->toCarbon(), verta()->submonths(3)->toCarbon()])->count(),
            ];
        });
        $this->fetchData['SelfRegistrationDoctors'] = User::newRegistredDoctor()->get()->take(10);
        $this->fetchData['transactiontotal'] = Transaction::todayTransaction()->sum('total_cost');
    }
    public function render()
    {
        $today_app =  AppointmentUser::today()->paginate(10);
        return view('admin::livewire.dashboard', ['today_app' => $today_app]);
    }
}
