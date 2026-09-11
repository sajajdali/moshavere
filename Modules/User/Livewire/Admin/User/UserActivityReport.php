<?php

namespace Modules\User\Livewire\Admin\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Front\app\Models\FeedBack;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\User\app\Models\UserWallet;
use Modules\User\Entities\User;

#[Title('گزارش جامع کاربر')]
class UserActivityReport extends Component
{
    use AuthorizesRequests, WithPagination;

    public User $user;

    #[Url(as: 'appointment_status')]
    public string $appointmentStatus = '';

    #[Url(as: 'call_result')]
    public string $callResult = '';

    #[Url(as: 'call_direction')]
    public string $callDirection = '';

    public function mount(User $user): void
    {
        $this->authorize('viewReport', $user);
        $this->user = $user->loadMissing('roles');
    }

    public function updatedAppointmentStatus(): void
    {
        $this->resetPage('appointments_page');
    }

    public function updatedCallResult(): void
    {
        $this->resetPage('calls_page');
    }

    public function updatedCallDirection(): void
    {
        $this->resetPage('calls_page');
    }

    public function resetFilters(): void
    {
        $this->reset('appointmentStatus', 'callResult', 'callDirection');
        $this->resetPage('appointments_page');
        $this->resetPage('calls_page');
    }

    public function render()
    {
        $appointmentSchema = Schema::hasTable('appointment_users');
        $callSchema = Schema::hasTable('appointment_call_logs');
        $walletSchema = Schema::hasTable('user_wallets');
        $feedbackSchema = Schema::hasTable('feedbacks');

        $patientAppointmentIds = $appointmentSchema
            ? AppointmentUser::withTrashed()->where('user_id', $this->user->id)->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value)->pluck('id')
            : collect();
        $providerAppointmentIds = $appointmentSchema
            ? AppointmentUser::withTrashed()->where('doctor_id', $this->user->id)->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value)->pluck('id')
            : collect();

        $appointmentBase = $appointmentSchema ? $this->appointmentQuery() : null;
        $callBase = $callSchema ? $this->callQuery($patientAppointmentIds, $providerAppointmentIds) : null;

        $appointmentCounts = $appointmentBase
            ? (clone $appointmentBase)->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status')
            : collect();
        $callCounts = $callBase
            ? (clone $callBase)->selectRaw('final_result, COUNT(*) as aggregate')->groupBy('final_result')->pluck('aggregate', 'final_result')
            : collect();

        $totalCalls = (int) $callCounts->sum();
        $successfulCalls = (int) ($callCounts['ANSWERED'] ?? 0);
        $failedCalls = $callBase
            ? (int) (clone $callBase)->where('final_result', '<>', 'ANSWERED')->duringAppointment()->count()
            : 0;
        $earlyCalls = $callBase ? (int) (clone $callBase)->early()->count() : 0;
        $reportableCalls = $successfulCalls + $failedCalls;
        $talkSeconds = $callBase ? (int) (clone $callBase)->sum('talk_duration_seconds') : 0;

        $walletBase = $walletSchema ? UserWallet::where('user_id', $this->user->id) : null;
        $walletBalance = $walletBase ? (int) ((clone $walletBase)->latest('id')->value('balance_after') ?? 0) : 0;
        $walletCredits = $walletBase ? (int) (clone $walletBase)->where('amount_change', '>', 0)->sum('amount_change') : 0;
        $walletDebits = $walletBase ? abs((int) (clone $walletBase)->where('amount_change', '<', 0)->sum('amount_change')) : 0;

        $feedbackBase = $feedbackSchema && $appointmentSchema
            ? FeedBack::whereHas('appointmentUser', fn (Builder $query) => $query->withTrashed()->where('user_id', $this->user->id)->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value))
            : null;
        $feedbackCount = $feedbackBase ? (int) (clone $feedbackBase)->count() : 0;
        $positiveFeedbacks = $feedbackBase ? (int) (clone $feedbackBase)->whereIn('answer', [0, 1, 2])->count() : 0;

        $appointments = $appointmentBase
            ? (clone $appointmentBase)
                ->with(['user:id,mobile', 'doctor:id,mobile', 'service', 'place', 'transaction'])
                ->when(\Modules\OnlineConsultation\Support\ConsultationAccess::schemaReady(['appointment_consultation_cases']), fn ($q) => $q->with('consultationCase'))
                ->when($this->appointmentStatus !== '', fn (Builder $query) => $query->where('status', $this->appointmentStatus))
                ->latest('date_visit')->paginate(12, ['*'], 'appointments_page')
            : null;

        $calls = $callBase
            ? (clone $callBase)
                ->with(['appointment' => fn ($query) => $query->withTrashed()->with(['user:id,mobile', 'doctor:id,mobile']), 'operator:id,mobile', 'consultantHangup', 'consultantNoAnswer'])
                ->when($this->callResult !== '', fn (Builder $query) => $query->where('final_result', $this->callResult))
                ->when($this->callDirection !== '', fn (Builder $query) => $query->where('direction', $this->callDirection))
                ->orderByRaw('COALESCE(call_entered_at, created_at) DESC')
                ->paginate(12, ['*'], 'calls_page')
            : null;

        $walletEntries = $walletBase
            ? (clone $walletBase)->with('user:id,mobile')->latest('id')->paginate(12, ['*'], 'wallet_page')
            : null;
        $feedbacks = $feedbackBase
            ? (clone $feedbackBase)->with(['appointmentUser' => fn ($query) => $query->withTrashed()->with('doctor:id,mobile')])
                ->latest('id')->paginate(12, ['*'], 'feedback_page')
            : null;

        return view('user::livewire.admin.user.user-activity-report', [
            'appointments' => $appointments,
            'calls' => $calls,
            'walletEntries' => $walletEntries,
            'feedbacks' => $feedbacks,
            'appointmentCounts' => $appointmentCounts,
            'callCounts' => $callCounts,
            'stats' => [
                'appointments' => (int) $appointmentCounts->sum(),
                'patient_appointments' => $patientAppointmentIds->count(),
                'provider_appointments' => $providerAppointmentIds->count(),
                'calls' => $totalCalls,
                'successful_calls' => $successfulCalls,
                'failed_calls' => $failedCalls,
                'early_calls' => $earlyCalls,
                'call_success_rate' => $reportableCalls > 0 ? round(($successfulCalls / $reportableCalls) * 100, 1) : 0,
                'talk_seconds' => $talkSeconds,
                'wallet_balance' => $walletBalance,
                'wallet_credits' => $walletCredits,
                'wallet_debits' => $walletDebits,
                'feedbacks' => $feedbackCount,
                'positive_feedbacks' => $positiveFeedbacks,
                'feedback_satisfaction' => $feedbackCount > 0 ? round(($positiveFeedbacks / $feedbackCount) * 100, 1) : 0,
            ],
            'schemas' => compact('appointmentSchema', 'callSchema', 'walletSchema', 'feedbackSchema'),
            'patientAppointmentIds' => $patientAppointmentIds,
            'providerAppointmentIds' => $providerAppointmentIds,
            'appointmentStatuses' => AppointmentUserStatusEnum::cases(),
            'callResultLabels' => $this->callResultLabels(),
            'walletTypeLabels' => $this->walletTypeLabels(),
        ]);
    }

    private function appointmentQuery(): Builder
    {
        return AppointmentUser::withTrashed()->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value)->where(function (Builder $query) {
            $query->where('user_id', $this->user->id)
                ->orWhere('doctor_id', $this->user->id);
        });
    }

    private function callQuery($patientAppointmentIds, $providerAppointmentIds): Builder
    {
        $mobile = preg_replace('/\D+/', '', (string) $this->user->mobile) ?? '';
        if (str_starts_with($mobile, '0098')) {
            $mobile = substr($mobile, 4);
        } elseif (str_starts_with($mobile, '98')) {
            $mobile = substr($mobile, 2);
        }
        $mobile = ltrim($mobile, '0');

        return AppointmentCallLog::query()
            ->where(function (Builder $query) {
                $query->whereNull('appointment_id')
                    ->orWhereHas('appointment', fn (Builder $appointment) => $appointment->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value));
            })
            ->where(function (Builder $query) use ($patientAppointmentIds, $providerAppointmentIds, $mobile) {
            if ($patientAppointmentIds->isNotEmpty()) {
                $query->orWhereIn('appointment_id', $patientAppointmentIds);
            }
            if ($providerAppointmentIds->isNotEmpty()) {
                $query->orWhereIn('appointment_id', $providerAppointmentIds);
            }
            $query->orWhere('operator_id', $this->user->id);
            if ($mobile !== '') {
                $normalizedColumn = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(patient_phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', ''), '0098', '')";
                $query->orWhereRaw($normalizedColumn.' LIKE ?', ['%'.$mobile]);
            }
            });
    }

    private function callResultLabels(): array
    {
        return [
            'ANSWERED' => 'موفق / پاسخ داده‌شده', 'NOANSWER' => 'بدون پاسخ', 'BUSY' => 'مشغول',
            'CALLER_ABANDONED' => 'پایان تماس قبل از اتصال', 'FAILED' => 'ناموفق',
            'CHANUNAVAIL' => 'مقصد در دسترس نیست', 'CONGESTION' => 'اختلال شبکه',
            'NOT_DIALED' => 'شماره‌گیری نشده', 'MISSING_EXTENSION' => 'داخلی تعریف نشده',
        ];
    }

    private function walletTypeLabels(): array
    {
        return [
            'credit' => 'افزایش اعتبار', 'purchase' => 'خرید', 'refund' => 'بازگشت وجه',
            'admin_adjustment' => 'اصلاح مدیر',
        ];
    }
}
