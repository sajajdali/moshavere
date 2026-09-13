<?php

namespace Modules\AppointmentUser\Livewire\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
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
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\Traits\OprationButtonsTrait;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\app\Exports\AppointmentListExport;
use Modules\Setting\Enum\SettingKeyEnum;

class AppointmentUserList extends Component
{
    use AuthorizesRequests, WithPagination, OprationButtonsTrait;
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
        'voip_followup_status' => null,
    ];
    public array $fetchData = [];
    public array $setting = [];
    public array $form = [];
    public array $quickTimeEdit = [
        'appointment_id' => null,
        'date' => null,
        'start_time' => null,
        'end_time' => null,
        'send_sms' => false,
    ];
    public array $quickTimeEditMeta = [];
    public array $consultationSummary = [];
    public array $financialSummary = [];
    public bool $showcollaps = true;
    public ?string $msg = null;
    #[Url]
    public string $sortField = 'date_visit';
    #[Url]
    public string $sortDirection = 'desc';
    public array $sortableColumns = [
        'id'          => 'شناسه',
        'date_visit'  => 'زمان نوبت',
        'service_id'  => 'بخش',
        'created_at'  => 'تاریخ ثبت',
        'status'      => 'وضعیت',
        'kind'        => 'نوع نوبت',
    ];
    public function sortBy($field)
    {
        if (! array_key_exists($field, $this->sortableColumns)) {
            return;
        }
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }
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
            'voip_followup_status' => null,
        ];
        $this->resetPage();
    }

    public function showTodayAppointments()
    {
        $this->resetProperties();
        $this->search['appointment_date'] = Verta::now()->format('Y/m/d');
    }

    public function openQuickTimeEdit(int $appointmentId): void
    {
        $appointment = AppointmentUser::with(['user', 'doctor'])->findOrFail($appointmentId);
        $this->authorize('update', $appointment);

        $this->resetValidation();
        $this->quickTimeEdit = [
            'appointment_id' => $appointment->id,
            'date' => verta($appointment->date_visit)->format('Y/m/d'),
            'start_time' => substr((string) $appointment->start_time, 0, 5),
            'end_time' => substr((string) $appointment->end_time, 0, 5),
            'send_sms' => false,
        ];
        $this->quickTimeEditMeta = [
            'patient' => $appointment->user?->full_name ?? 'کاربر حذف شده',
            'doctor' => $appointment->doctor?->full_name ?? 'پزشک حذف شده',
            'tracking_code' => $appointment->tracking_code,
        ];

        $this->dispatch('openQuickTimeEditModal');
    }

    public function saveQuickTimeEdit(): void
    {
        $validated = $this->validate([
            'quickTimeEdit.appointment_id' => ['required', 'integer'],
            'quickTimeEdit.date' => ['required', 'string'],
            'quickTimeEdit.start_time' => ['required', 'date_format:H:i'],
            'quickTimeEdit.end_time' => ['required', 'date_format:H:i', 'after:quickTimeEdit.start_time'],
            'quickTimeEdit.send_sms' => ['boolean'],
        ], [
            'quickTimeEdit.date.required' => 'تاریخ نوبت را انتخاب کنید.',
            'quickTimeEdit.start_time.required' => 'ساعت شروع را وارد کنید.',
            'quickTimeEdit.start_time.date_format' => 'فرمت ساعت شروع صحیح نیست.',
            'quickTimeEdit.end_time.required' => 'ساعت پایان را وارد کنید.',
            'quickTimeEdit.end_time.date_format' => 'فرمت ساعت پایان صحیح نیست.',
            'quickTimeEdit.end_time.after' => 'ساعت پایان باید بعد از ساعت شروع باشد.',
        ]);

        try {
            $date = Verta::parse($validated['quickTimeEdit']['date'])->toCarbon();
        } catch (\Throwable) {
            $this->addError('quickTimeEdit.date', 'تاریخ انتخاب‌شده معتبر نیست.');

            return;
        }

        $appointment = AppointmentUser::findOrFail($validated['quickTimeEdit']['appointment_id']);
        $this->authorize('update', $appointment);

        $startTime = $validated['quickTimeEdit']['start_time'];
        $endTime = $validated['quickTimeEdit']['end_time'];
        [$hour, $minute] = array_map('intval', explode(':', $startTime));
        $oldDate = $appointment->date_visit->copy();

        DB::transaction(function () use ($appointment, $date, $hour, $minute, $startTime, $endTime): void {
            $appointment->update([
                'date_visit' => Carbon::instance($date)->setTime($hour, $minute)->toDateTimeString(),
                'start_time' => $startTime . ':00',
                'end_time' => $endTime . ':00',
            ]);
        });

        $appointment->refresh();
        $appointment->setting?->runGenerateCacheJob(specialDayConvert($oldDate));
        if (! $appointment->date_visit->isSameDay($oldDate)) {
            $appointment->setting?->runGenerateCacheJob(specialDayConvert($appointment->date_visit));
        }

        $smsQueued = false;
        if ($validated['quickTimeEdit']['send_sms']) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_TIME_UPDATE);
            if (filled($smsTemplate) && filled($appointment->user?->mobile)) {
                $appointment->notify(new AppointmentSmsNotification($smsTemplate));
                $smsQueued = true;
            }
        }

        $this->msg = match (true) {
            $smsQueued => 'زمان نوبت با موفقیت تغییر کرد و پیامک تغییر زمان در صف ارسال قرار گرفت.',
            $validated['quickTimeEdit']['send_sms'] => 'زمان نوبت تغییر کرد؛ اما قالب پیامک یا شماره موبایل بیمار در دسترس نبود.',
            default => 'زمان نوبت با موفقیت تغییر کرد.',
        };
        $this->dispatch('closeQuickTimeEditModal');
        $this->dispatch('loadJs');
    }

    #[Computed]
    private function handleSearch($isExported = false)
    {
        $permisstion_check = auth()->user();
        $query = AppointmentUser::query();
        if (\Modules\OnlineConsultation\Support\ConsultationAccess::schemaReady(['appointment_consultation_cases', 'appointment_billing_records'])) {
            $query->with(['consultationCase', 'billingRecord']);
        }
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
        if (filled($this->search['voip_followup_status'] ?? null)) {
            $query->where('kind', AppointmentUserKindEnum::VOIP)
                ->whereRaw('TIMESTAMP(DATE(date_visit), COALESCE(end_time, TIME(date_visit))) < ?', [now()->toDateTimeString()]);
            $missingSettlement = fn ($q) => $q->whereDoesntHave('billingRecord')
                ->orWhereHas('billingRecord', fn ($billing) => $billing->where('refund_status', '!=', 'completed'));
            $missingCompletion = fn ($q) => $q->whereDoesntHave('consultationCase')
                ->orWhereHas('consultationCase', fn ($case) => $case->where('state', 'OPEN'));
            match ($this->search['voip_followup_status']) {
                'settlement_missing' => $query->where($missingSettlement),
                'completion_missing' => $query->where($missingCompletion),
                'both_missing' => $query->where($missingSettlement)->where($missingCompletion),
                'incomplete_any' => $query->where(fn ($q) => $q->where($missingSettlement)->orWhere($missingCompletion)),
                'completed_both' => $query
                    ->whereHas('billingRecord', fn ($billing) => $billing->where('refund_status', 'completed'))
                    ->whereHas('consultationCase', fn ($case) => $case->whereIn('state', ['COMPLETED', 'PATIENT_NO_SHOW'])),
                default => null,
            };
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
        $applySort = function ($query) {
            if ($this->sortField === 'date_visit') {
                return $query->orderByRaw('DATE(date_visit) ' . $this->sortDirection . ', TIME(date_visit) ' . $this->sortDirection);
            }
            if (array_key_exists($this->sortField, $this->sortableColumns)) {
                return $query->orderBy($this->sortField, $this->sortDirection);
            }
            return $query->orderByRaw('DATE(date_visit) DESC, TIME(date_visit) ASC');
        };

        if ($isExported) {
            if (collect($this->search)->each(fn($item) => $item != null)) {
                return  $appointments = $applySort($query)->get();
            }
            return $appointments =  $applySort($query)->get();
        }
        $appointments = $applySort($query)->paginate(10);
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
        $this->fetchData['feedbackVoiceUrl'] = $appointmentUser->surveyVoiceUrl();
        $this->fetchData['feedbackIsVoip'] = $appointmentUser->isStoredFromVoip();
        $this->dispatch('lunchFeedBackModal', true);
    }

    public function openConsultationSummary(int $appointmentId): void
    {
        $appointment = AppointmentUser::with(['user', 'doctor', 'consultationCase', 'callLogs', 'billingRecord.adjustments'])
            ->findOrFail($appointmentId);

        abort_unless($appointment->kind === AppointmentUserKindEnum::VOIP, 404);

        $calls = $appointment->callLogs;
        $answered = $calls->where('final_result', 'ANSWERED');
        $unanswered = $calls->filter(fn ($call) => $call->countsAsUnanswered());
        $lastCall = $calls->sortByDesc('call_entered_at')->first();
        $talkSeconds = (int) $calls->sum('talk_duration_seconds');
        $billing = app(\Modules\OnlineConsultation\Services\AppointmentBillingService::class)->ensure($appointment)
            ?? $appointment->billingRecord;
        $billing?->loadMissing('adjustments');
        $effectiveRefund = $billing?->refund_status === 'completed'
            ? max(0, (int) $billing->refunded_amount + (int) $billing->adjustments->sum('amount_change'))
            : (int) ($billing?->suggested_refund_amount ?? 0);

        $this->consultationSummary = [
            'id' => $appointment->id,
            'tracking_code' => $appointment->tracking_code ?: $appointment->id,
            'patient' => $appointment->user?->full_name ?? 'کاربر حذف شده',
            'doctor' => $appointment->doctor?->full_name ?? 'پزشک حذف شده',
            'date' => $appointment->date_visit ? verta($appointment->date_visit)->format('Y/m/d H:i') : '—',
            'completed' => $appointment->hasCompletedPhoneConsultation(),
            'answered' => $answered->isNotEmpty(),
            'calls_count' => $calls->count(),
            'answered_count' => $answered->count(),
            'unanswered_count' => $unanswered->count(),
            'early_count' => $calls->filter(fn ($call) => $call->isEarlyCall())->count(),
            'talk_duration' => sprintf('%02d:%02d:%02d', intdiv($talkSeconds, 3600), intdiv($talkSeconds % 3600, 60), $talkSeconds % 60),
            'last_call_at' => $lastCall?->call_entered_at ? verta($lastCall->call_entered_at)->format('Y/m/d H:i:s') : '—',
            'financial_calculated' => $billing?->refund_status === 'completed',
            'financial_status' => $billing?->refund_status === 'completed' ? 'تسویه مالی انجام شده' : 'محاسبه مالی تکمیل نشده',
            'appointment_amount' => (int) ($billing?->total_paid_amount ?? 0),
            'refund_amount' => $effectiveRefund,
            'practitioner_amount' => (int) ($billing?->practitioner_earned_amount ?? 0),
            'platform_amount' => (int) ($billing?->platform_profit_amount ?? 0),
            'details_url' => route('admin.consultation.call-reports.appointment', $appointment),
        ];

        $this->dispatch('openConsultationSummaryModal');
    }

    public function openFinancialSummary(int $appointmentId): void
    {
        $appointment = AppointmentUser::with(['user', 'doctor', 'transaction', 'billingRecord.adjustments'])
            ->findOrFail($appointmentId);

        $billing = $appointment->billingRecord;
        if (in_array($appointment->kind, [AppointmentUserKindEnum::VOIP, AppointmentUserKindEnum::ONLINE], true)) {
            $billing = app(\Modules\OnlineConsultation\Services\AppointmentBillingService::class)->ensure($appointment) ?? $billing;
            $billing?->loadMissing('adjustments');
        }

        $appointmentAmount = (int) ($billing?->total_paid_amount
            ?: data_get($appointment->details, AppointmentUser::DETAIL_PAYMENT.'.'.AppointmentUser::DETAIL_PAYMENT_PRICE.'.int', 0)
            ?: $appointment->transaction?->total_cost
            ?: $appointment->transaction?->cost
            ?: 0);
        $effectiveRefund = $billing
            ? max(0, (int) $billing->refunded_amount + (int) $billing->adjustments->sum('amount_change'))
            : 0;
        $refundAmount = $billing?->refund_status === 'completed'
            ? $effectiveRefund
            : (int) ($billing?->suggested_refund_amount ?? 0);
        $financialState = match (true) {
            $billing?->refund_status === 'completed' => 'تسویه نهایی‌شده',
            $billing?->refund_status === 'approved' => 'محاسبه تأییدشده؛ در انتظار واریز',
            $billing !== null => 'در انتظار محاسبه و تأیید',
            $appointment->status === AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT => 'در انتظار پرداخت بیمار',
            $appointment->status === AppointmentUserStatusEnum::STATUS_SUCCESSFUL => 'پرداخت / ثبت نوبت تأیید شده',
            default => 'فاقد تسویه مالی',
        };

        $this->financialSummary = [
            'appointment_id' => $appointment->id,
            'tracking_code' => $appointment->tracking_code ?: $appointment->id,
            'patient' => $appointment->user?->full_name ?? 'کاربر حذف شده',
            'doctor' => $appointment->doctor?->full_name ?? 'پزشک حذف شده',
            'state' => $financialState,
            'settled' => $billing?->refund_status === 'completed',
            'appointment_amount' => $appointmentAmount,
            'used_amount' => (int) ($billing?->used_amount ?? $appointmentAmount),
            'refund_amount' => $refundAmount,
            'refund_label' => $billing?->refund_status === 'completed' ? 'مبلغ برگشتی قطعی' : 'مبلغ برگشتی پیشنهادی',
            'net_amount' => max(0, $appointmentAmount - $refundAmount),
            'practitioner_amount' => (int) ($billing?->practitioner_earned_amount ?? 0),
            'platform_amount' => (int) ($billing?->platform_profit_amount ?? 0),
            'reserved_minutes' => (int) ($billing?->reserved_minutes ?? 0),
            'used_minutes' => $billing ? max(0, (int) $billing->reserved_minutes - (int) $billing->system_unused_minutes) : 0,
            'details_url' => $appointment->kind === AppointmentUserKindEnum::VOIP
                ? route('admin.consultation.call-reports.appointment', $appointment)
                : null,
        ];

        $this->dispatch('openFinancialSummaryModal');
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
        $this->setting['show_description'] =  ! is_null(setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_SHOW_DESCRIPTION_IN_APP_LIST)) &&
        setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_SHOW_DESCRIPTION_IN_APP_LIST) ;
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
