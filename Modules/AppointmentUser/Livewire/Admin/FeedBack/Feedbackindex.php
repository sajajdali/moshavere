<?php

namespace Modules\AppointmentUser\Livewire\Admin\FeedBack;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Modules\User\Enum\UserMetaEnum;
use Modules\Front\app\Models\FeedBack;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class Feedbackindex extends Component
{
    #[Url]
    public $search = [];

    public array $fetchData = [];
    public array $form = [];

    public function startSearch()
    {
        $this->render();
    }
    public function resetProperties()
    {
        $this->search = [];
        $this->render();
    }
    #[On('delete')]
    public function deletePlace(FeedBack $model)
    {
        $model->delete();
        return redirect()->route('admin.appointment.feedback')->with('success', 'نظر حذف شد');
    }

    public function showModal($id)
    {
        $appointmentUSer = AppointmentUser::find($id);
        if(isset($appointmentUSer)) {
            $this->fetchData['feedbacks'] = $appointmentUSer->feedbacks;
            $this->dispatch('lunchFeedBackModal', true);
        }else{
            $this->dispatch('appointmentNotFound',true);
        }
    }

    public function render()
    {
        $query =  FeedBack::query();
        $searchCriteria = [
            'idSearch' => [
                'condition' => isset($this->search['id']),
                'callback' => function ($query) {
                    return $query->whereId($this->search['id']);
                },
            ],
            'search.userName' => [
                'condition' => isset($this->search['userName']),
                'callback' => function ($query) {
                    return $query->whereHas('appointmentUser', function ($qq) {
                        $qq->whereHas('user', function ($qqq) {
                            $qqq->whereHas('metas', function ($qqqq) {
                                $qqqq->where([
                                    ['meta_key', UserMetaEnum::LAST_NAME],
                                    ['meta_value', 'LIKE', "%{$this->search['userName']}%"],
                                ])->orWhere([
                                    ['meta_key', UserMetaEnum::FIRST_NAME],
                                    ['meta_value', 'LIKE', "%{$this->search['userName']}%"],
                                ]);
                            });
                        });
                    });
                },
            ],
            'search.doctorName' => [
                'condition' => isset($this->search['doctorName']),
                'callback' => function ($query) {
                    return $query->whereHas('appointmentUser', function ($qq) {
                        $qq->whereHas('doctor', function ($qqq) {
                            $qqq->whereHas('metas', function ($qqqq) {
                                $qqqq->where([
                                    ['meta_key', UserMetaEnum::LAST_NAME],
                                    ['meta_value', 'LIKE', "%{$this->search['doctorName']}%"],
                                ])->orWhere([
                                    ['meta_key', UserMetaEnum::FIRST_NAME],
                                    ['meta_value', 'LIKE', "%{$this->search['doctorName']}%"],
                                ]);
                            });
                        });
                    });
                },
            ],
            'search.serviceName' => [
                'condition' => isset($this->search['serviceName']),
                'callback' => function ($query) {
                    return $query->whereHas('appointmentUser', function ($qq) {
                        $qq->whereHas('service', function ($qqq) {
                            $qqq->where('title', 'LIKE', "%{$this->search['serviceName']}%");
                        });
                    });
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
        // Get all feedbacks and group by appointment_user_id
        $feedbacksGrouped = $query->orderByDesc('id')->get()->groupBy('appointment_user_id');

        // Convert grouped data to a paginated collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10; // Number of groups per page
        $currentItems = $feedbacksGrouped->slice(($currentPage - 1) * $perPage, $perPage);

        $paginatedFeedbacks = new LengthAwarePaginator(
            $currentItems,
            $feedbacksGrouped->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view(
            'appointmentuser::livewire.admin.feed-back.feedbackindex',
            ['feedBacks' => $paginatedFeedbacks]
        );
    }
}
