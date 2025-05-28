<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\User\Entities\User;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Modules\User\Enum\UserMetaEnum;
use Maatwebsite\Excel\Facades\Excel;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Service\app\Models\Service;
use Illuminate\Support\Facades\Validator;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Traits\OprationButtonsTrait;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\app\Exports\AppointmentListExport;

class AppointmentUserList extends Component
{
    use WithPagination, OprationButtonsTrait;
    #[Url]
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
        'kind'                 => null,
    ];
    public array $fetchData = [];
    public array $form = [];
    public bool $showcollaps = true;
    public ?string $msg = null;
    public function startSearch()
    {
        $this->showcollaps = true;
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
            'docNumber'            => null,
            'setterAppointment'    => null,
            'section_status'       => null,
            'Doc_id'               => null,
            'AppointmentStatus'    => null,
            'kind'                 => null,
        ];
        $this->resetPage();
    }
    #[Computed]
    private function handleSearch($isExported = false)
    {
        $this->msg = '';
        $this->resetErrorBag();
        $permisstion_check = auth()->user();
        $query = AppointmentUser::query();
        $searchCriteria = [
            'permition' => [
                'condition' => ! $permisstion_check->isAdmin(),
                'callback' => function ($query) use ($permisstion_check) {
                    if (! $permisstion_check->can('appointment_user') && $permisstion_check->can('appointment_user.own')) {
                        return $query->where('doctor_id', $permisstion_check->id);
                    } else {
                        return;
                    }
                },
            ],
            'search.appointment_id' => [
                'condition' => isset($this->search['appointment_id']),
                'callback' => function ($query) {
                    return $query->whereId($this->search['appointment_id']);
                },

            ],
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
            'national_code' => [
                'condition' => isset($this->search['national_code']),
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($q) {
                        $q->whereHas('metas', function ($qq) {
                            $qq->where([
                                ['meta_key', UserMetaEnum::NATIONAL_CODE],
                                ['meta_value', 'LIKE', "%{$this->search['national_code']}%"],
                            ]);
                        });
                    });
                },
            ],
            'mobile' => [
                'condition' => $this->search['user_mobile'],
                'callback' => function ($query) {
                    return $query->whereHas('user', function ($q) {
                        $q->where('mobile', 'LIKE', "%{$this->search['user_mobile']}%");
                    });
                },
            ],
            'kind' => [
                'condition' => $this->search['kind'],
                'callback' => function ($query) {
                    return $query->where('kind', $this->search['kind']);
                },
            ],
            'appointment_date' => [
                'condition' => $this->search['appointment_date'],
                'callback' => function ($query) {
                    try {
                        $appointmentDate =  Verta::parse($this->search['appointment_date'])->toCarbon();
                    } catch (\Throwable $th) {
                        $this->addError('exelError', 'فرمت تاریخ انتخابی صحیح نیست،با گزینه نمایش همه ، فیلتر ها را پاک کنید');
                        $this->dispatch('dateFormatWrong', true);
                        return;
                    }

                    return $query->whereDate('date_visit', $appointmentDate);
                },
            ],
            'appointment_set_date' => [
                'condition' => $this->search['appointment_set_date'],
                'callback' => function ($query) {
                    try {
                        $appointment_set_date =  Verta::parse($this->search['appointment_set_date'])->toCarbon();
                    } catch (\Throwable $th) {
                        $this->addError('exelError', 'فرمت تاریخ انتخابی صحیح نیست،با گزینه نمایش همه ، فیلتر ها را پاک کنید');
                        $this->dispatch('dateFormatWrong', true);
                        return;
                    }
                    return $query->whereDate('created_at', $appointment_set_date);
                },
            ],
            'appointment_end_date' => [
                'condition' => $this->search['appointment_end_date'],
                'callback' => function ($query) {
                    try {
                        $appointment_end_date =  Verta::parse($this->search['appointment_end_date'])->toCarbon();
                    } catch (\Throwable $th) {
                        $this->addError('exelError', 'فرمت تاریخ انتخابی صحیح نیست،با گزینه نمایش همه ، فیلتر ها را پاک کنید');
                        $this->dispatch('dateFormatWrong', true);
                        return;
                    }
                    return $query->whereDate('date_visit', '<=', $appointment_end_date);
                },
            ],
            'appointment_star_date' => [
                'condition' => $this->search['appointment_star_date'],
                'callback' => function ($query) {
                    try {
                        $appointment_star_date =  Verta::parse($this->search['appointment_star_date'])->toCarbon();
                    } catch (\Throwable $th) {
                        $this->addError('exelError', 'فرمت تاریخ انتخابی صحیح نیست،با گزینه نمایش همه ، فیلتر ها را پاک کنید');
                        $this->dispatch('dateFormatWrong', true);
                        return;
                    }
                    return $query->whereDate('date_visit', '>=', $appointment_star_date);
                },
            ],
            'AppointmentStatus' => [
                'condition' => isset($this->search['AppointmentStatus']) && $this->search['AppointmentStatus'] != null,
                'callback' => function ($query) {
                    return $query->where('status', AppointmentUserStatusEnum::tryFrom($this->search['AppointmentStatus']));
                },
            ],
            'appointment_operatorId' => [
                'condition' => isset($this->search['appointment_operatorId']),
                'callback' => function ($query) {
                    if ($this->search['appointment_operatorId'] == 0) {
                        return $query->where('operator_id', null);
                    } else {
                        return $query->where('operator_id', $this->search['appointment_operatorId']);
                    }
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
                    $setertAppRole = Role::find($this->search['setterAppointment']);
                    if ($setertAppRole) {
                        return $query->whereHas('agent', function ($qq) use ($setertAppRole) {
                            $qq->whereHas('roles', function ($qqq) use ($setertAppRole) {
                                return $qqq->where('id', $setertAppRole->id);
                            });
                        });
                    }
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
        if (auth()->user()->can('appointment_user.online') && auth()->user()->cannot('appointment_user.list')) {
            $query->where(function ($q) {
                $q->where('kind', AppointmentUserKindEnum::ONLINE);
            });
        }
        if (auth()->user()->can('appointment_user.own') && auth()->user()->cannot('appointment_user.list')) {
            $query->where(function ($q) {
                $q->where('agent_id', auth()->user()->id);
            });
        }
        if ($isExported) {
            return $appointments =  $query->orderByDesc('id')->get();
        }
        $appointments =  $query->orderByDesc('id')->paginate(10);
        return $appointments;
    }
    public function ExportData()
    {
        $collection = $this->handleSearch(true);
        if (count($collection) > 500) {
            return $this->dispatch('exelError', true);
            $this->addError('exelError', 'مقدار اطلاعات بیشتر از حد مجاز است، لطفا با استفاده از جست و جوی تاریخ، تعداد نوبت ها را محدود تر کنید');
        }
        return  Excel::download(new AppointmentListExport($collection), 'appointment_lists.xlsx');
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
            'refundPayment' => $this->refuntPaiedApp($model),
            'resendPaymentSms' => $this->resendPaymentSms($model),
            default => '',
        };
    }

    //opdation button
    private function redirectToPage($msg)
    {
        $this->msg = $msg;
        $this->render();
        // return redirect()->route('admin.appointment_user.list')->with('success', $msg);
    }
    //opdation button functions end

    public function lunchFeedBackModal(AppointmentUser $appointmentUser)
    {
        $this->fetchData['feedbacks'] = $appointmentUser->feedbacks;
        $this->dispatch('lunchFeedBackModal', true);
    }
    public function booted()
    {
        $this->dispatch('loadJs', true);
    }
    #[Computed]
    public function segmentData($appUserId)
    {
        $app = AppointmentUser::find($appUserId);
        return $app->details[AppointmentUser::DETAIL_SEGMENTS];

    }
    public function mount()
    {
        $this->fetchData['appointmentSetter'] = Role::all();
        $this->fetchData['Services'] = Service::all();
        $this->fetchData['doctors'] = User::doctors();

        if (request()->has('search')) {
            $seaechInputs =  request()->input('search');
            if (isset($seaechInputs['kind']) && $seaechInputs['kind'] === "2" && isset($seaechInputs['AppointmentStatus']) && $seaechInputs['AppointmentStatus'] === "0") {
                $this->showcollaps = false;
            }
        }
    }

    public function render()
    {
        // handle search pannel with defining new search Critera ;
        $query = $this->handleSearch();
        return view('appointmentuser::livewire.admin.appointment-user-list');
    }
}
