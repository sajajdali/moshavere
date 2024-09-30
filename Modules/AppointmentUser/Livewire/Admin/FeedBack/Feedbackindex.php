<?php

namespace Modules\AppointmentUser\Livewire\Admin\FeedBack;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Modules\User\Enum\UserMetaEnum;
use Modules\Front\app\Models\FeedBack;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;

class Feedbackindex extends Component
{
    use WithPagination;
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
        if (isset($appointmentUSer)) {
            $this->fetchData['feedbacks'] = $appointmentUSer->feedbacks;
            $this->dispatch('lunchFeedBackModal', true);
        } else {
            $this->dispatch('appointmentNotFound', true);
        }
    }

    public function mount()
    {
        $appointmentQuestion = FeedBack::whereHas('appointmentUser', function ($q) {
            return $q->where('kind', AppointmentUserKindEnum::ONLINE);
        })->where('question', 1);
        $this->fetchData['onlineApp']['like'] = $appointmentQuestion->whereIn('answer',  [1,2,3])->count();
        $this->fetchData['onlineApp']['dislike'] = $appointmentQuestion->where('answer', 4)->count();
        $doctorQuery =  FeedBack::where('question', 2);
        $this->fetchData['onlineDoc']['like'] = $doctorQuery->whereIn('answer',  [1,2,3])->count();
        $this->fetchData['onlineDoc']['dislike'] = $doctorQuery->where('answer', 4)->count();

        $inPersonApp = FeedBack::whereHas('appointmentUser', function ($q) {
            return $q->where('kind', AppointmentUserKindEnum::IN_PERSION);
        })->where('question', 2);
        $this->fetchData['inPerson_app']['like'] = $inPersonApp->whereIn('answer',  [1,2,3])->count();
        $this->fetchData['inPerson_app']['dislike'] = $inPersonApp->where('answer', 4)->count();
        $doctorQuery =  FeedBack::where('question', 2);
        $this->fetchData['inPerson_doc']['like'] = $inPersonApp->whereIn('answer',  [1,2,3])->count();
        $this->fetchData['inPerson_doc']['dislike'] = $inPersonApp->where('answer', 4)->count();

    }
    public function showSpecialFeedback($section)
    {
        $this->dispatch('scrollToTop', true);
        $this->fetchData['likeorDislike'] = [];
        match ($section) {
            'onlineLike' => $this->fetchData['likeorDislike']['ShowOnlineLike'] = true,
            'onlineDocLike' => $this->fetchData['likeorDislike']['ShowOnlineDocLike'] = true,
            'onlineAppdislike' => $this->fetchData['likeorDislike']['onlineAppdislike'] = true,
            'onlineDocdislike' => $this->fetchData['likeorDislike']['onlineDocdislike'] = true,
            'inPerson_applike' => $this->fetchData['likeorDislike']['inPerson_applike'] = true,
            'inPerson_doclike' => $this->fetchData['likeorDislike']['inPerson_doclike'] = true,
            'inPerson_appdislike' => $this->fetchData['likeorDislike']['inPerson_appdislike'] = true,
            'inPerson_docdislike' => $this->fetchData['likeorDislike']['inPerson_docdislike'] = true,
            default => ''
        };
        return $this->render();
    }
    public function render()
    {
        $query =  Feedback::query();

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
            'onlineLikedAppointment' => [
                'condition' => isset($this->fetchData['likeorDislike']['ShowOnlineLike']),
                'callback' => function ($query) {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::ONLINE);
                    })->where('question', 1)->where(function ($q) {
                        return $q->whereIn('answer',  [1,2,3]);
                    });
                },
            ],
            'ShowOnlineDocLike' => [
                'condition' => isset($this->fetchData['likeorDislike']['ShowOnlineDocLike']),
                'callback' => function ($query): mixed {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::ONLINE);
                    })->where('question', 2)->where(function ($q) {
                        return $q->whereIn('answer',  [1,2,3]);
                    });
                },
            ],
            'onlineAppdislike' => [
                'condition' => isset($this->fetchData['likeorDislike']['onlineAppdislike']),
                'callback' => function ($query): mixed {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::ONLINE);
                    })->where('question', 1)->where(function ($q) {
                        return $q->where('answer', 4);
                    });
                },
            ],
            'onlineDocdislike' => [
                'condition' => isset($this->fetchData['likeorDislike']['onlineDocdislike']),
                'callback' => function ($query): mixed {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::ONLINE);
                    })->where('question', 2)->where(function ($q) {
                        return $q->where('answer', 4);
                    });
                },
            ],
            'inPerson_applike' => [
                'condition' => isset($this->fetchData['likeorDislike']['inPerson_applike']),
                'callback' => function ($query): mixed {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::IN_PERSION);
                    })->where('question', 1)->where(function ($q) {
                        return $q->whereIn('answer',  [1,2,3]);
                    });
                },
            ],
            'inPerson_doclike' => [
                'condition' => isset($this->fetchData['likeorDislike']['inPerson_doclike']),
                'callback' => function ($query): mixed {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::IN_PERSION);
                    })->where('question', 2)->where(function ($q) {
                        return $q->whereIn('answer',  [1,2,3]);
                    });
                },
            ],
            'inPerson_appdislike' => [
                'condition' => isset($this->fetchData['likeorDislike']['inPerson_appdislike']),
                'callback' => function ($query): mixed {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::IN_PERSION);
                    })->where('question', 1)->where(function ($q) {
                        return $q->whereIn('answer',  4);
                    });
                },
            ],
            'inPerson_docdislike' => [
                'condition' => isset($this->fetchData['likeorDislike']['inPerson_docdislike']),
                'callback' => function ($query): mixed {
                    return $query->whereHas('appointmentUser', function ($q) {
                        return $q->where('kind', AppointmentUserKindEnum::IN_PERSION);
                    })->where('question', 2)->where(function ($q) {
                        return $q->whereIn('answer',  4);
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
        $query->select('appointment_user_id', DB::raw('MAX(answer) as answer'), DB::raw('MAX(question) as question'))
        ->groupBy('appointment_user_id')->orderBy('created_at', 'desc') ;
        return view(
            'appointmentuser::livewire.admin.feed-back.feedbackindex',
            ['feedBacks' => $query->paginate(10)]
        );
    }
}
