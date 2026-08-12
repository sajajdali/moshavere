<?php

namespace Modules\Admin\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Api\app\Models\VoipIncoming;
use Modules\Setting\Enum\SettingKeyEnum;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Transaction\app\Models\Transaction;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;

#[Title('پیشخوان مدیریت')]
class Dashboard extends Component
{
    use WithPagination;

    public array $fetchData = [];
    public string $selectedAppointmentDate;
    public bool $dashboardDataLoaded = false;

    public function mount()
    {
        $this->selectedAppointmentDate = verta()->format('Y/m/d');
    }

    public function loadDashboardData()
    {
        $cacheKey = 'admin.dashboard.summary.' . auth()->id();
        $this->fetchData = Cache::remember($cacheKey, now()->addMinute(), function () {
            return $this->buildDashboardData();
        });

        $this->dashboardDataLoaded = true;
        $this->dispatch('dashboardDataLoaded', charts: [
            'month' => data_get($this->fetchData, 'chart.month', []),
            'successful' => data_get($this->fetchData, 'chart.data.successful', []),
            'canceled' => data_get($this->fetchData, 'chart.data.canceld', []),
            'incoming_labels' => collect(data_get($this->fetchData, 'incoming_calls_chart', []))->pluck('label')->values()->all(),
            'incoming_counts' => collect(data_get($this->fetchData, 'incoming_calls_chart', []))->pluck('count')->values()->all(),
            'week_labels' => collect(data_get($this->fetchData, 'appointments_week_chart', []))->pluck('label')->values()->all(),
            'week_counts' => collect(data_get($this->fetchData, 'appointments_week_chart', []))->pluck('count')->values()->all(),
            'status_labels' => collect(data_get($this->fetchData, 'appointment_status_chart', []))->pluck('label')->values()->all(),
            'status_counts' => collect(data_get($this->fetchData, 'appointment_status_chart', []))->pluck('count')->values()->all(),
        ]);
    }

    private function buildDashboardData(): array
    {
        $fetchData = [];

        //scope functions can be found  in the models
        $fetchData['today_appointment'] = AppointmentUser::today()->DoctorPermittion()->successful()->count();
        $fetchData['appointment_insights'] = [
            'next_7_days' => AppointmentUser::DoctorPermittion()
                ->whereBetween('date_visit', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->count(),
            'wait_payment' => AppointmentUser::DoctorPermittion()
                ->where('status', AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT)
                ->count(),
            'month_revenue' => Transaction::where('status', TransactionStatusEnum::SUCCESSFUL)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_cost'),
            'today_cancel' => AppointmentUser::DoctorPermittion()
                ->where('status', AppointmentUserStatusEnum::STATUS_CANCEL)
                ->whereDate('date_visit', now())
                ->count(),
        ];
        $fetchData['appointment_status_chart'] = collect([
            AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
            AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
            AppointmentUserStatusEnum::STATUS_CANCEL,
            AppointmentUserStatusEnum::STATUS_MONITORING,
        ])->map(function (AppointmentUserStatusEnum $status) {
            return [
                'label' => $status->getName(),
                'count' => AppointmentUser::DoctorPermittion()->where('status', $status)->count(),
            ];
        })->toArray();
        $fetchData['appointments_week_chart'] = collect(range(0, 6))->map(function ($daysAhead) {
            $date = now()->addDays($daysAhead);

            return [
                'label' => verta($date)->format('m/d'),
                'count' => AppointmentUser::DoctorPermittion()->whereDate('date_visit', $date)->count(),
            ];
        })->toArray();
        $fetchData['recent_appointments'] = AppointmentUser::DoctorPermittion()
            ->with(['user', 'doctor'])
            ->latest('id')
            ->take(5)
            ->get();
        $fetchData['recent_transactions'] = Transaction::with('user')
            ->latest('id')
            ->take(5)
            ->get();
        $fetchData['incoming_call_status'] = filter_var(setting(SettingKeyEnum::VOIP_APPOINTMENT_STATUS), FILTER_VALIDATE_BOOLEAN);
        if ($fetchData['incoming_call_status']) {
            $fetchData['incoming_calls'] = [
                'today' => VoipIncoming::whereDate('created_at', now())->count(),
                'total' => VoipIncoming::count(),
                'latest' => VoipIncoming::latest('id')->first()?->incoming,
            ];
            $fetchData['incoming_calls_chart'] = collect(range(6, 0))->map(function ($daysAgo) {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => verta($date)->format('m/d'),
                    'count' => VoipIncoming::whereDate('created_at', $date)->count(),
                ];
            })->toArray();
        }
        $fetchData['chart']['month'] = [verta()->format('F'), verta()->submonths(1)->format('F'), verta()->submonths(2)->format('F'), verta()->submonths(3)->format('F')];
        $fetchData['chart']['data']['successful'] = [
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(1)->toCarbon(), verta()->toCarbon()])->count(),
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(2)->toCarbon(), verta()->submonths(1)->toCarbon()])->count(),
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(3)->toCarbon(), verta()->submonths(2)->toCarbon()])->count(),
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL)->whereBetween('date_visit', [verta()->submonths(4)->toCarbon(), verta()->submonths(3)->toCarbon()])->count(),
        ];

        $fetchData['chart']['data']['canceld'] =  [
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(1)->toCarbon(), verta()->toCarbon()])->count(),
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(2)->toCarbon(), verta()->submonths(1)->toCarbon()])->count(),
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(3)->toCarbon(), verta()->submonths(2)->toCarbon()])->count(),
            AppointmentUser::DoctorPermittion()->where('status', AppointmentUserStatusEnum::STATUS_CANCEL)->whereBetween('date_visit', [verta()->submonths(4)->toCarbon(), verta()->submonths(3)->toCarbon()])->count(),
        ];
        if (auth()->user()->isAdmin()) {
            $fetchData['SelfRegistrationDoctors'] = User::newRegistredDoctor()->take(10)->get();
            $fetchData['transactiontotal'] = Transaction::todayTransaction()->sum('total_cost');
            $apiToken = setting(SettingKeyEnum::SMS_API_TOKEN);
            if (isset($apiToken)) {
                try {
                    $response = Http::withToken($apiToken)
                        ->connectTimeout(1)
                        ->timeout(1)
                        ->get('https://shsms.ir/api/v1/budget');
                } catch (\Throwable $th) {
                }
            }
            if (
                isset($response)
                && isset($response['status'])
                && $response['status'] == true
            ) {
                $fetchData['shsms'] = $response['data']['ballance'];
            }
        }

        return $fetchData;
    }

    public function updatedSelectedAppointmentDate()
    {
        if (! $this->dashboardDataLoaded) {
            return;
        }

        $this->resetPage();
    }

    public function selectAdjacentAppointmentDate(string $direction): void
    {
        if (! $this->dashboardDataLoaded) {
            return;
        }

        try {
            $selectedDate = Verta::parse($this->selectedAppointmentDate)->toCarbon()->toDateString();
        } catch (\Throwable $th) {
            $selectedDate = now()->toDateString();
        }

        $query = AppointmentUser::DoctorPermittion()
            ->whereDate('date_visit', $direction === 'next' ? '>' : '<', $selectedDate);

        $appointment = $direction === 'next'
            ? $query->orderBy('date_visit')->first()
            : $query->orderByDesc('date_visit')->first();

        if ($appointment) {
            $this->selectedAppointmentDate = verta($appointment->date_visit)->format('Y/m/d');
            $this->dispatch('dashboardAppointmentDateChanged', date: $this->selectedAppointmentDate);
            $this->resetPage();
        }
    }

    private function selectedAppointmentDateCarbon()
    {
        try {
            return Verta::parse($this->selectedAppointmentDate)->toCarbon();
        } catch (\Throwable $th) {
            $this->selectedAppointmentDate = verta()->format('Y/m/d');

            return now();
        }
    }

    public function render()
    {
        if ($this->dashboardDataLoaded) {
            $today_app =  AppointmentUser::DoctorPermittion()
                ->whereDate('date_visit', $this->selectedAppointmentDateCarbon())
                ->orderBy('date_visit')
                ->paginate(10);
        } else {
            $today_app = new LengthAwarePaginator([], 0, 10);
        }

        return view('admin::livewire.dashboard', ['today_app' => $today_app]);
    }
}
