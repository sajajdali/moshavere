<?php

namespace Modules\Front\Livewire\Admin\Comment;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Modules\User\Enum\UserMetaEnum;
use Illuminate\Support\Facades\Cache;
use Modules\Front\app\Models\Comment;
use Modules\Front\app\Models\FeedBack;
use Modules\Front\enum\CommentStatusEnum;
use Modules\Front\enum\CommentShowHomePage;
use Modules\AppointmentUser\app\Models\AppointmentUser;

class Commentlivewire extends Component
{
    #[Url]
    public $search = [];

    public array $fetchData = [];
    public array $form = [];
    public  $alertMessage = false;

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
    public function deletePlace(Comment $model)
    {
        $this->authorize('delete', $model);
        Cache::forget('homepageComments');
        $model->delete();
        return redirect()->route('admin.comment')->with('success', 'نظر حذف شد');
    }


    public function lunchModal(Comment $comment)
    {
        $this->fetchData['operation']['comment'] = $comment;
        if (isset($this->fetchData['operation']['comment']->reply)) {
            $this->form['reply'] = $this->fetchData['operation']['comment']->reply;
        }
    }
    public function removeReply(Comment $comment)
    {
        $comment->update(['reply' => null]);
        $this->alertMessage = 'پاسخ با موفقیت حذف شد';
        $this->dispatch('closeModal', true);
    }
    public function storeAnswer()
    {
        $this->validate(['form.reply' => 'required|string']);
        if (isset($this->fetchData['operation']['comment'])) {
            $this->fetchData['operation']['comment']->update(['reply' => $this->form['reply']]);
            $this->dispatch('closeModal', true);
            $this->alertMessage = 'پاسخ با موفقیت ثبت شد';
        }
    }

    public function approveComment(Comment $comment)
    {
        $this->authorize('update', $comment);
        $comment->update([
            'status' => CommentStatusEnum::ACCEPTED,
        ]);
        $this->alertMessage = ' با موفقیت تایید شد';
    }
    public function disaprovedComment(Comment $comment)
    {
        $this->authorize('update', $comment);
        $comment->update([
            'status' => CommentStatusEnum::REJECTED,
        ]);
        $this->alertMessage = ' با موفقیت لغو تایید شد';
    }
    public function showInHopePage(Comment $comment,$status)
    {
        $this->authorize('update', $comment);

        $h_status  = CommentShowHomePage::tryFrom($status) ; 
        $comment->update([
            'show_in_homePage' => $h_status,
        ]);
        Cache::forget('homepageComments');
        if($status == 1 ) {
            $this->alertMessage = 'نظر در صفحه اصلی نمایش داده میشود';
        }else{
            $this->alertMessage = 'نظر در صفحه اصلی نمایش داده نمیشود';
        }
    }
    public function boot()
    {
        if (isset($this->alertMessage)) {
            $this->alertMessage = false;
        }
    }
    public function render()
    {
        $permitionCheck = auth()->user();
        $query =  Comment::query();
        $searchCriteria = [
            'permition' => [
                'condition' => ! $permitionCheck->isAdmin() && $permitionCheck->can('comment.own') ,
                'callback' => function ($query) use($permitionCheck) {
                    return $query->whereHas('doctor',function($q) use($permitionCheck) {
                        return $q->where('id',$permitionCheck->id);
                    });
                },
            ],
            'idSearch' => [
                'condition' => isset($this->search['id']),
                'callback' => function ($query) {
                    return $query->whereId($this->search['id']);
                },
            ],
            'search.userName' => [
                'condition' => isset($this->search['userName']),
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($qqq) {
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
                },
            ],
            'search.doctorName' => [
                'condition' => isset($this->search['doctorName']),
                'callback' => function ($query) {
                    return $query->whereHas('doctor', function ($qqq) {
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
            'search.hopePageShowStatus' => [
                'condition' => isset($this->search['hopePageShowStatus']),
                'callback' => function ($query) {
                    return $query->where('show_in_homePage', CommentShowHomePage::tryFrom($this->search['hopePageShowStatus']));
                },
            ],
            'search.reply' => [
                'condition' => isset($this->search['reply']),
                'callback' => function ($query) {
                    if($this->search['reply'] == 'true') {
                        return $query->whereNotNull('reply');
                    }else{
                        return $query->whereNull('reply');
                    }
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


        return view('front::livewire.admin.comment.commentlivewire', ['comments' => $query->orderByDesc('status')->paginate(10)]);
    }
}
