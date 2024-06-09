<?php

namespace Modules\Front\Livewire\Admin\Comment;

use Livewire\Component;
use Livewire\Attributes\Url;
use Modules\User\Enum\UserMetaEnum;
use Modules\Front\app\Models\Comment;
use Modules\Front\app\Models\FeedBack;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class Commentlivewire extends Component
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
        $this->fetchData['feedbacks'] = $appointmentUSer->feedbacks;
        $this->dispatch('lunchFeedBackModal', true);
    }

    public function lunchModal(Comment $comment)
    {
        $this->fetchData['operation']['comment'] = $comment;
    }
    public function storeAnswer()
    {
        $this->validate(['form.reply' => 'required|string']);
        if (isset($this->fetchData['operation']['comment'])) {
            $this->fetchData['operation']['comment']->update(['reply' => $this->form['reply']]);
            $this->dispatch('closeModal', true);
            $this->dispatch('message', message:'پاسخ با موفقیت برای این کامنت ذخیره شد!');
        }
    }

    public function render()
    {

        $query =  Comment::query();
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


        return view('front::livewire.admin.comment.commentlivewire', ['comments' => $query->paginate(10)]);
    }
}
