<?php

namespace Modules\AppointmentUser\Livewire\Admin\Online;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Modules\AppointmentUser\app\Models\AppointmentOnline;

#[Title('پیام های پشتیبانی')]
class AppointmentOnlineMessagesList extends Component
{
    public array $search = [];
    public array $fetchData = [];


    #[Computed]
    public function handleSearch()
    {
        $query = AppointmentOnline::query();
        $searchCriteria = [
            'user_id_search' => [
                'condition' => $this->search['user_id'],
                'callback' => function ($query) {
                    return $query->where('user_id', $this->search['user_id']);
                },
            ],
            'user_first_name' => [
                'condition' => $this->search['user_first_name'],
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($q) {
                        $q->whereHas('metas', function ($qq) {
                            $qq->where([
                                ['meta_key', UserMetaEnum::FIRST_NAME],
                                ['meta_value', 'LIKE', "%{$this->search['user_first_name']}%"],
                            ]);
                        });
                    });
                },
            ],
            'user_last_name' => [
                'condition' => $this->search['user_last_name'],
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($q) {
                        $q->whereHas('metas', function ($qq) {
                            $qq->where([
                                ['meta_key', UserMetaEnum::LAST_NAME],
                                ['meta_value', 'LIKE', "%{$this->search['user_last_name']}%"],
                            ]);
                        });
                    });
                },
            ],
            'mobile' => [
                'condition' => $this->search['user_mobile'],
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($q) {
                        $q->whereHas('metas', function ($qq) {
                            $qq->where([
                                ['meta_key', UserMetaEnum::MOBILE],
                                ['meta_value', 'LIKE', "%{$this->search['user_mobile']}%"],
                            ]);
                        });
                    });
                },
            ],
            'appointment_date' => [
                'condition' => $this->search['appointment_date'],
                'callback' => function ($query) {
                    return $query->whereDate('date_visit', Verta::parse($this->search['appointment_date'])->toCarbon());
                },
            ],
            'appointment_set_date' => [
                'condition' => $this->search['appointment_set_date'],
                'callback' => function ($query) {
                    return $query->whereDate('created_at', Verta::parse($this->search['appointment_set_date'])->toCarbon());
                },
            ],
            'appointment_end_date' => [
                'condition' => $this->search['appointment_end_date'],
                'callback' => function ($query) {
                    return $query->whereDate('created_at', '<', Verta::parse($this->search['appointment_end_date'])->toCarbon());
                },
            ],
            'appointment_star_date' => [
                'condition' => $this->search['appointment_star_date'],
                'callback' => function ($query) {
                    return $query->whereDate('created_at', '>', Verta::parse($this->search['appointment_star_date'])->toCarbon());
                },
            ],
            'AppointmentStatus' => [
                'condition' => $this->search['AppointmentStatus'],
                'callback' => function ($query) {
                    return $query->where('status', AppointmentUserStatusEnum::tryFrom($this->search['AppointmentStatus']));
                },
            ],
        ];

        foreach ($searchCriteria as $property => $config) {
            $condition = $config['condition'];
            $callback = $config['callback'];
            if (!empty($condition)) {
                $query->when($condition, $callback);
            }
        }
        $appointments =  $query->paginate(10);
        return $appointments;
    }
    public function booted()
    {
        $this->dispatch('loadJs', true);
    }
    public function mount()
    {
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.online.appointment-online-messages-list');
    }
}
