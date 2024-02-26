<?php

namespace Modules\AppointmentSetting\Livewire\UserAppointMentList;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public array $search = [];
    public $searchPanel = "";

    public function startSearch() {
        $this->render();
    }
    public function resetProperties() {
        $this->search = [];
        $this->render();
    }

    public function render()
    {
        $query = null;
        /*
        handle search pannel with defining new search Critera ;
            $query = Appointment::orderByDesc('id');
            $searchCriteria = [
                'mobileSearch' => [
                   'condition' => $this->search['user_id'],
                   'callback' => function ($query) {
                        return $query->whereIn('user_id', $this->search['user_id'])->whereNotNull('date_send')->orderByDesc('id');
                    },
                ],
            ];

            foreach ($searchCriteria as $property => $config) {
                $condition = $config['condition'];
                $callback = $config['callback'];
                if ($condition) {
                    $query->when($condition, $callback);
                }
            }
        */
        return view('appointmentsetting::livewire.user-appoint-ment-list.index', [
            // 'appointments' => $query->paginate(10),
        ]);
    }
}
