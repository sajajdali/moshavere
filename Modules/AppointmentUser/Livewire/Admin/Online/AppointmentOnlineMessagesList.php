<?php

namespace Modules\AppointmentUser\Livewire\Admin\Online;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Modules\User\Enum\UserMetaEnum;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Support\Facades\Validator;
use Modules\AppointmentUser\Traits\OprationButtonsTrait;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessage;

#[Title('پیام های پشتیبانی')]
class AppointmentOnlineMessagesList extends Component
{
    use WithPagination;
    use OprationButtonsTrait;
    public array $search = [
        'user_id' => null,
        'user_first_name' => null,
        'user_last_name' => null,
        'user_mobile' => null,
        'appointment_date' => null,
        'appointment_set_date' => null,
        'appointment_end_date' => null,
        'appointment_star_date' => null,
        'AppointmentStatus' => null,
        'appointment_messages' => null,
    ];
    public array $form = [];
    public array $fetchData = [
        'showCaceledApp' => true,
    ];
    public function startSearch()
    {
        $this->handleSearch();
        $this->render();
    }
    public function resetProperties()
    {
        $this->search = [];
        $this->handleSearch();
        $this->render();
    }
    public function showalltheMessages()
    {
        $this->fetchData['showCaceledApp'] = false;
    }
    #[Computed]
    public function handleSearch()
    {
        // $query = AppointmentOnlineMessage::where('type',1)
        $query = AppointmentOnlineMessage::query()
            ->when(isset($this->fetchData['showCaceledApp']) && $this->fetchData['showCaceledApp'] == true, function ($q) {
                $q->whereHas('online', function ($qq) {
                    $qq->whereIn('status', [
                        AppointmentOnlineStatusEnum::PENDING,
                        AppointmentOnlineStatusEnum::ACCEPTED,
                        AppointmentOnlineStatusEnum::REPLY_BY_USER,
                        AppointmentOnlineStatusEnum::ANSWER_BY_DOCTOR,
                        AppointmentOnlineStatusEnum::REACTIVATED
                    ]);
                });
            })->when(isset($this->fetchData['showCaceledApp']) && $this->fetchData['showCaceledApp'] == false, function ($q) {
                $q->whereHas('online', function ($qq) {
                    $qq->whereIn('status', [
                        AppointmentOnlineStatusEnum::REJECT,
                        AppointmentOnlineStatusEnum::CANCEL,
                        AppointmentOnlineStatusEnum::COMPLETED_BY_DOCTOR,
                        AppointmentOnlineStatusEnum::TIME_IS_OVER,
                    ]);
                });
            })
            ->when(isset($this->search['user_id']), function ($q) {
                return $q->where('user_id', $this->search['user_id']);
            })->when(isset($this->search['user_first_name']), function ($q) {
                return $q->whereHas('user', function ($qq) {
                    return $qq->whereHas('metas', function ($qqq) {
                        return $qqq->where([
                            ['meta_key', UserMetaEnum::FIRST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['user_first_name']}%"],
                        ]);
                    });
                });
            })->when(isset($this->search['user_last_name']), function ($q) {
                return $q->whereHas('user', function ($qq) {
                    return $qq->whereHas('metas', function ($qqq) {
                        return $qqq->where([
                            ['meta_key', UserMetaEnum::LAST_NAME],
                            ['meta_value', 'LIKE', "%{$this->search['user_last_name']}%"],
                        ]);
                    });
                });
            })->when(isset($this->search['search-docNumberId']), function ($q) {
                return $q->whereHas('user', function ($qq) {
                    return $qq->whereHas('metas', function ($qqq) {
                        return $qqq->where([
                            ['meta_key', UserMetaEnum::DOCUMENT_NUMBER],
                            ['meta_value', 'LIKE', "%{$this->search['search-docNumberId']}%"],
                        ]);
                    });
                });
            })->when(isset($this->search['user_mobile']), function ($q) {
                return $q->whereHas('user', function ($q) {
                    return $q->where('mobile', 'LIKE', "%{$this->search['user_mobile']}%");
                });
            })->when(isset($this->search['AppointmentStatus']), function ($q) {
                $q->whereHas('online', function ($q) {
                    $q->where('status', AppointmentOnlineStatusEnum::tryFrom($this->search['AppointmentStatus']));
                });
            })->when(isset($this->search['appointment_date']), function ($q) {
                //  $date = $this->search['appointment_date'];
                // $validate  = Validator::make(['appointment_date' => $date], [
                //     'appointment_date' => 'date',
                // ]);
                // if ($validate->fails()) {
                //     $this->addError('msgerror', 'فرمت تاریخ وارد شده صحیح نیست');
                // }
            })->when(isset($this->search['appointment_messages']), function ($q) {
                return $q->where('body', 'LIKE', "%{$this->search['appointment_messages']}%");
            })
            ->selectRaw('appointment_online_id, MAX(id) as id,MAX(user_id) as user_id,MAX(type) as type,MAX(seen) as seen,MAX(body) as body,MAX(updated_at) as updated_at')
            ->groupBy('appointment_online_id')
            ->orderByDesc('updated_at');

        return  $query->paginate(100);
    }
    public function booted()
    {
        $this->dispatch('loadJs', true);
    }
    private function redirectToPage($msg)
    {
        return redirect()->route('admin.appointment_user.message.list')->with('success', $msg);
    }
    public function render()
    {
        return view('appointmentuser::livewire.admin.online.appointment-online-messages-list');
    }
}
