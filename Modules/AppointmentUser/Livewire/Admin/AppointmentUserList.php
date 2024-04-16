<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\User\Entities\User;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;
use Maatwebsite\Excel\Facades\Excel;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\app\Exports\AppointmentListExport;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;

class AppointmentUserList extends Component
{
    use WithPagination;
    public array $search = [
        'user_id'              => null,
        'user_first_name'      => null,
        'user_last_name'       => null,
        'user_mobile'          => null,
        'appointment_date'     => null,
        'appointment_set_date' => null,
        'appointment_star_date' => null,
        'appointment_end_date' => null,
        'AppointmentStatus'    => null,
        'docNumber'            => null,
        'setterAppointment'    => null,
        'section_status'       => null,
        'Doc_id'               => null,
    ];
    public array $fetchData = [];
    public array $form = [];

    public function startSearch()
    {
        $this->render();
    }
    public function resetProperties()
    {
        $this->search = [
            'user_id'              => null,
            'user_first_name'      => null,
            'user_last_name'       => null,
            'user_mobile'          => null,
            'appointment_date'     => null,
            'appointment_set_date' => null,
            'appointment_star_date' => null,
            'appointment_end_date' => null,
            'AppointmentStatus'    => null,
            'docNumber'            => null,
            'setterAppointment'    => null,
            'section_status'       => null,
            'Doc_id'               => null,
        ];
        $this->resetPage();
    }
    #[Computed]
    private function handleSearch()
    {
        $query = AppointmentUser::query();
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
            'docNumber' => [
                'condition' => $this->search['docNumber'],
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($q) {
                        $q->whereHas('metas', function ($qq) {
                            $qq->where([
                                ['meta_key', UserMetaEnum::DOCUMENT_NUMBER],
                                ['meta_value', 'LIKE', "%{$this->search['docNumber']}%"],
                            ]);
                        });
                    });
                },
            ],
            'setterAppointment' => [
                'condition' => $this->search['setterAppointment'],
                'callback' => function ($query) {
                    return $query->whereJsonContains('details->appointment_via', $this->search['setterAppointment']);
                },
            ],
            'section_status' => [
                'condition' => $this->search['section_status'],
                'callback' => function ($query) {
                    return $query->where('service_id', $this->search['section_status']);
                },
            ],
            'Doc_id' => [
                'condition' => $this->search['Doc_id'],
                'callback' => function ($query) {
                    return $query->where('doctor_id', $this->search['Doc_id']);
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
    public function ExportData()
    {

        if ($this->handleSearch()->getCollection()->count() > 2000) {
            return $this->addError('exelError', 'مقدار اطلاعات بیشتر از حد مجاز است، لطفا با استفاده از جست و جوی تاریخ، تعداد نوبت ها را محدود تر کنید');
        }
        return  Excel::download(new AppointmentListExport($this->handleSearch()->getCollection()), 'appointment_lists.xlsx');
    }
    #[On('confirm_swal')]
    public function swal_confirm($action, $model)
    {
        return match ($action) {
            'GroupCancel' => $this->cancelSelectedApp(),
            'changeType' => $this->changeType($model),
            'cancelWithSms' => $this->cancelAppointment($model, true),
            'cancelWithOutSms' => $this->cancelAppointment($model, false),
            'delete' => $this->cancelAndDeleteApp($model),
            default => '',
        };
    }
    public function cancelSelectedApp()
    {
        foreach ($this->form['checkbox'] as $AppID => $checked) {
            if ($checked) {
                $app = AppointmentUser::find($AppID);
                $app->update(['status' => AppointmentUserStatusEnum::STATUS_CANCEL]);
            }
        }
        return  $this->redirectToPage('نوبت های انتخابی با موفقیت کنسل شدند');
    }
    public function changeType($id)
    {
        $app = AppointmentUser::find($id);
        $app->update(['type' =>  AppointmentUserTypeEnum::BETWEEN_PATIENTS]);
        return  $this->redirectToPage('نوبت به بین مریض تغییر پیدا کرد');
    }
    public function cancelAppointment($id, $sendSmsStatus)
    {
        $app = AppointmentUser::find($id);
        $app->update(['status' =>  AppointmentUserStatusEnum::STATUS_CANCEL]);
        if ($sendSmsStatus) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_CANCEL);
            if (isset($smsTemplate)) {
                $app->notify(new AppointmentSmsNotification($smsTemplate));
            }
        }
        return  $this->redirectToPage('نوبت با موفقیت کنسل شد');
    }
    public function cancelAndDeleteApp($id)
    {

        $this->cancelAppointment($id, true);
        $app = AppointmentUser::find($id);
        $app->delete();
        $this->redirectToPage('نوبت با موفقیت حذف شد');
    }
    public function ApproveOnlineAppointment($id)
    {
        $app = AppointmentUser::find($id);
        $onlineApp = AppointmentOnline::firstWhere('appointment_user_id',$app->id);
        $onlineApp->update(['status' => AppointmentOnlineStatusEnum::ACCEPTED]);
        $app->update(['status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL]);
        $this->redirectToPage('نوبت با موفقیت تایید شد');
    }
    public function disApproveOnlineAppointment($id)
    {
        $this->fetchData['disapproveId'] = $id;
        $this->dispatch('lunchModal', true);
        $app = AppointmentUser::find($id);
        $app->update(['status'=>AppointmentUserStatusEnum::STATUS_DISAPPROVED]) ;
        $this->redirectToPage('ضعیت نوبت به عدم تایید ، تغییر پیدا کرد');
    }
    public function disaprovedModal()
    {
        $app = AppointmentUser::find($this->fetchData['disapproveId']);
        $detail = $app->details;
        if (isset($this->form['reason'])) {
            if (isset($detail)) {
                $detail = array_merge($detail, [AppointmentUser::DISAPPROVED_DESCRIPTION => $this->form['reason']]);
            } else {
                $detail = [AppointmentUser::DISAPPROVED_DESCRIPTION => $this->form['reason'] ];
            }
        }
        try {
            $onlineApp = AppointmentOnline::firstWhere('appointment_user_id',$app->id);
            $onlineApp->update(['status' => AppointmentOnlineStatusEnum::REJECT]);
            $app->update(['status' => AppointmentUserStatusEnum::STATUS_DISAPPROVED, 'details' =>  $detail]);
        } catch (\Exception $th) {
            return redirect()->route('admin.appointment_user.list')->with('error', 'خطا در به روز رسانی');
        }
        $this->redirectToPage('وضعیت نوبت به عدم تایید ، تغییر پیدا کرد');
    }
    public function ignoreDisaproveModal()
    {
        unset($this->fetchData['disapproveId']);
    }
    private function redirectToPage($msg)
    {
        return redirect()->route('admin.appointment_user.list')->with('success', $msg);
    }
    public function booted()
    {
        $this->dispatch('loadJs', true);
    }
    public function mount()
    {

        // TODO::pass roles that can set appointmet in appointmentSetter property ;
        $this->fetchData['appointmentSetter'] = Role::find(1)->users;
        $this->fetchData['Services'] = Service::all();
        $this->fetchData['doctors'] = User::doctors();
    }

    public function render()
    {
        // handle search pannel with defining new search Critera ;
        $query = $this->handleSearch();
        return view(
            'appointmentuser::livewire.admin.appointment-user-list'

        );
    }
}
