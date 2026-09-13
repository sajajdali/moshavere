<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\AppointmentConsultantHangup;
use Modules\OnlineConsultation\Models\AppointmentConsultantNoAnswer;
use Modules\OnlineConsultation\Models\AppointmentBillingRecord;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Services\AppointmentBillingService;
use Modules\OnlineConsultation\Models\AppointmentConsultationReport;
use Modules\OnlineConsultation\Services\ConsultationCaseService;
use Modules\OnlineConsultation\Services\ConsultationCallbackService;
use Modules\OnlineConsultation\Models\AppointmentCallbackRequest;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class CallReportController extends Controller
{
    public function index(Request $request)
    {
        $shortCallThresholdSeconds = max(0, (int) ConsultationSetting::current()->ignored_short_call_minutes) * 60;
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'result' => ['nullable', 'string', 'in:ANSWERED,NOANSWER,BUSY,CALLER_ABANDONED,FAILED,CHANUNAVAIL,CONGESTION,NOT_DIALED,MISSING_EXTENSION'],
            'from' => ['nullable', 'string', 'max:10'],
            'to' => ['nullable', 'string', 'max:10'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        try {
            $fromDate = filled($filters['from'] ?? null) ? \Verta::parse($filters['from'])->toCarbon()->startOfDay() : null;
            $toDate = filled($filters['to'] ?? null) ? \Verta::parse($filters['to'])->toCarbon()->endOfDay() : null;
        } catch (\Throwable) {
            throw ValidationException::withMessages(['from' => 'تاریخ انتخاب‌شده معتبر نیست.']);
        }
        if ($fromDate && $toDate && $toDate->lt($fromDate)) {
            throw ValidationException::withMessages(['to' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.']);
        }

        $base = AppointmentCallLog::query()
            ->where(function ($query) {
                $query->whereNull('appointment_id')
                    ->orWhereHas('appointment', fn ($appointment) => $appointment->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value));
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('patient_phone', 'like', '%'.$search.'%')
                        ->orWhere('call_id', 'like', '%'.$search.'%')
                        ->orWhereHas('appointment', fn ($q) => $q->where('tracking_code', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['result'] ?? null, fn ($q, $result) => $q->where('final_result', $result))
            ->when($filters['doctor_id'] ?? null, fn ($q, $doctorId) => $q->whereHas('appointment', fn ($appointment) => $appointment->where('doctor_id', $doctorId)))
            ->when($fromDate, fn ($q, $from) => $q->where('call_entered_at', '>=', $from))
            ->when($toDate, fn ($q, $to) => $q->where('call_entered_at', '<=', $to));

        $stats = [
            'calls' => (clone $base)->count(),
            'appointments' => (clone $base)->whereNotNull('appointment_id')->distinct()->count('appointment_id'),
            'answered' => (clone $base)->where('final_result', 'ANSWERED')->count(),
            'talk_seconds' => (int) (clone $base)->sum('talk_duration_seconds'),
            'average_talk_seconds' => (int) round((float) ((clone $base)->where('final_result', 'ANSWERED')->avg('talk_duration_seconds') ?? 0)),
        ];

        $appointments = (clone $base)
            ->whereNotNull('appointment_id')
            ->selectRaw('appointment_id, COUNT(*) as calls_count')
            ->selectRaw("SUM(CASE WHEN final_result = 'ANSWERED' THEN 1 ELSE 0 END) as answered_count")
            ->selectRaw("SUM(CASE WHEN final_result <> 'ANSWERED' AND ((call_entered_at IS NOT NULL AND appointment_start_at IS NOT NULL AND call_entered_at >= appointment_start_at AND (appointment_end_at IS NULL OR call_entered_at <= appointment_end_at)) OR ((call_entered_at IS NULL OR appointment_start_at IS NULL) AND appointment_state = 'IN_APPOINTMENT_TIME')) THEN 1 ELSE 0 END) as unanswered_count")
            ->selectRaw("SUM(CASE WHEN (call_entered_at IS NOT NULL AND appointment_start_at IS NOT NULL AND call_entered_at < appointment_start_at) OR ((call_entered_at IS NULL OR appointment_start_at IS NULL) AND appointment_state IN ('BEFORE_APPOINTMENT', 'WAITING_FOR_APPOINTMENT')) THEN 1 ELSE 0 END) as early_count")
            ->selectRaw('SUM(talk_duration_seconds) as talk_seconds, SUM(wait_duration_seconds) as wait_seconds')
            ->selectRaw('MAX(call_entered_at) as last_call_at')
            ->groupBy('appointment_id')
            ->orderByDesc('last_call_at')
            ->paginate(20)
            ->withQueryString();

        $appointmentModels = AppointmentUser::with(['user', 'doctor'])
            ->whereIn('id', $appointments->getCollection()->pluck('appointment_id'))
            ->get()->keyBy('id');
        $appointmentHangups = AppointmentConsultantHangup::query()
            ->whereIn('appointment_id', $appointments->getCollection()->pluck('appointment_id'))
            ->whereHas('callLog', fn ($query) => $query->duringAppointment()->where('talk_duration_seconds', '<=', $shortCallThresholdSeconds))
            ->selectRaw('appointment_id, COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN hangup_via = 'PHONE' THEN 1 ELSE 0 END) as phone_count")
            ->selectRaw("SUM(CASE WHEN hangup_via = 'SOFTPHONE' THEN 1 ELSE 0 END) as softphone_count")
            ->selectRaw('MAX(hung_up_at) as last_hung_up_at')
            ->groupBy('appointment_id')
            ->get()->keyBy('appointment_id');
        $appointmentNoAnswers = AppointmentConsultantNoAnswer::query()
            ->whereIn('appointment_id', $appointments->getCollection()->pluck('appointment_id'))
            ->whereHas('callLog', fn ($query) => $query->duringAppointment())
            ->selectRaw('appointment_id, COUNT(*) as total_count, MAX(no_answer_at) as last_no_answer_at')
            ->groupBy('appointment_id')
            ->get()->keyBy('appointment_id');
        $appointments->getCollection()->transform(function ($row) use ($appointmentModels, $appointmentHangups, $appointmentNoAnswers) {
            $row->appointment = $appointmentModels->get($row->appointment_id);
            $row->consultant_hangups = $appointmentHangups->get($row->appointment_id);
            $row->consultant_no_answers = $appointmentNoAnswers->get($row->appointment_id);
            return $row;
        });

        $unlinkedCalls = (clone $base)->whereNull('appointment_id')->latest('call_entered_at')->limit(10)->get();

        $callbackAvailable = ConsultationAccess::schemaReady(['appointment_callback_requests']);
        $callbackRequests = collect();
        $stats['callback_requests'] = 0;
        if ($callbackAvailable) {
            $callbackBase = AppointmentCallbackRequest::query()
                ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('patient_phone', 'like', '%'.$search.'%')->orWhere('request_id', 'like', '%'.$search.'%')->orWhereHas('appointment', fn ($appointment) => $appointment->where('tracking_code', 'like', '%'.$search.'%'))))
                ->when($filters['doctor_id'] ?? null, fn ($q, $doctorId) => $q->whereHas('appointment', fn ($appointment) => $appointment->where('doctor_id', $doctorId)))
                ->when($fromDate, fn ($q, $from) => $q->where('requested_at', '>=', $from))
                ->when($toDate, fn ($q, $to) => $q->where('requested_at', '<=', $to));
            $stats['callback_requests'] = (clone $callbackBase)->count();
            $callbackRequests = $callbackBase->with(['appointment.user', 'appointment.doctor', 'requester', 'callLog'])
                ->latest('requested_at')->limit(50)->get();
        }

        $managementIncidents = AppointmentConsultantHangup::with(['appointment.user', 'appointment.doctor'])
            ->whereHas('appointment', fn ($appointment) => $appointment->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value))
            ->whereHas('callLog', fn ($query) => $query->duringAppointment()->where('talk_duration_seconds', '<=', $shortCallThresholdSeconds))
            ->latest('hung_up_at')->limit(25)->get()
            ->map(function ($incident) {
                $incident->incident_type = 'HANGUP';
                $incident->incident_at = $incident->hung_up_at;
                return $incident;
            })
            ->concat(
                AppointmentConsultantNoAnswer::with(['appointment.user', 'appointment.doctor'])
                    ->whereHas('appointment', fn ($appointment) => $appointment->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value))
                    ->whereHas('callLog', fn ($query) => $query->duringAppointment())
                    ->latest('no_answer_at')->limit(25)->get()
                    ->map(function ($incident) {
                        $incident->incident_type = 'NO_ANSWER';
                        $incident->incident_at = $incident->no_answer_at;
                        return $incident;
                    })
            )
            ->sortByDesc('incident_at')
            ->take(50)
            ->values();

        $practitioners = ConsultationPractitioner::with('user')->where('active', true)
            ->whereNotNull('extension')->orderBy('display_name')->get();

        $noShowAppointments = AppointmentUser::with(['user', 'doctor', 'consultationCase.completedBy', 'billingRecord'])
            ->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value)
            ->whereHas('consultationCase', fn ($q) => $q->where('state', 'PATIENT_NO_SHOW'))
            ->when($filters['doctor_id'] ?? null, fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('tracking_code', 'like', '%'.$search.'%')->orWhereHas('user', fn ($u) => $u->where('mobile', 'like', '%'.$search.'%'))))
            ->when($fromDate, fn ($q, $from) => $q->where('date_visit', '>=', $from))
            ->when($toDate, fn ($q, $to) => $q->where('date_visit', '<=', $to))
            ->latest('date_visit')->paginate(15, ['*'], 'no_show_page')->withQueryString();

        return view('onlineconsultation::call-reports.index', compact('noShowAppointments', 'appointments', 'unlinkedCalls', 'callbackRequests', 'managementIncidents', 'stats', 'filters', 'practitioners', 'shortCallThresholdSeconds'));
    }

    public function appointment(AppointmentUser $appointment, AppointmentBillingService $billingService, ConsultationCaseService $caseService, ConsultationCallbackService $callbackService)
    {
        $shortCallThresholdSeconds = max(0, (int) ConsultationSetting::current()->ignored_short_call_minutes) * 60;
        $appointment->load(['user', 'doctor.consultationPractitioner']);
        $alternatePhonesAvailable = ConsultationAccess::schemaReady(['appointment_alternate_phones']);
        if ($alternatePhonesAvailable) {
            $appointment->load('alternatePhones.creator');
        } else {
            // Keep appointment details available during a rolling deployment,
            // before the tenant migration for alternate phones has completed.
            $appointment->setRelation('alternatePhones', collect());
        }
        $query = AppointmentCallLog::where('appointment_id', $appointment->id);
        $allCalls = (clone $query)->get();
        $stats = [
            'calls' => $allCalls->count(),
            'answered' => $allCalls->where('final_result', 'ANSWERED')->count(),
            'unanswered' => $allCalls->filter(fn ($call) => $call->countsAsUnanswered())->count(),
            'early' => $allCalls->filter(fn ($call) => $call->isEarlyCall())->count(),
            'talk_seconds' => (int) (clone $query)->sum('talk_duration_seconds'),
            'wait_seconds' => (int) (clone $query)->sum('wait_duration_seconds'),
            'average_talk_seconds' => (int) round((float) ((clone $query)->where('final_result', 'ANSWERED')->avg('talk_duration_seconds') ?? 0)),
            'last_call_at' => (clone $query)->max('call_entered_at'),
            'consultant_hangups' => AppointmentConsultantHangup::where('appointment_id', $appointment->id)->whereHas('callLog', fn ($query) => $query->duringAppointment()->where('talk_duration_seconds', '<=', $shortCallThresholdSeconds))->count(),
            'phone_hangups' => AppointmentConsultantHangup::where('appointment_id', $appointment->id)->where('hangup_via', AppointmentConsultantHangup::VIA_PHONE)->whereHas('callLog', fn ($query) => $query->duringAppointment()->where('talk_duration_seconds', '<=', $shortCallThresholdSeconds))->count(),
            'softphone_hangups' => AppointmentConsultantHangup::where('appointment_id', $appointment->id)->where('hangup_via', AppointmentConsultantHangup::VIA_SOFTPHONE)->whereHas('callLog', fn ($query) => $query->duringAppointment()->where('talk_duration_seconds', '<=', $shortCallThresholdSeconds))->count(),
            'completed_consultant_hangups' => AppointmentConsultantHangup::where('appointment_id', $appointment->id)->whereHas('callLog', fn ($query) => $query->duringAppointment()->where('talk_duration_seconds', '>', $shortCallThresholdSeconds))->count(),
            'consultant_no_answers' => AppointmentConsultantNoAnswer::where('appointment_id', $appointment->id)->whereHas('callLog', fn ($query) => $query->duringAppointment())->count(),
        ];
        $calls = $query->with(['operator', 'consultantHangup', 'consultantNoAnswer'])->latest('call_entered_at')->paginate(20);
        $hangupIncidents = AppointmentConsultantHangup::where('appointment_id', $appointment->id)->whereHas('callLog', fn ($query) => $query->duringAppointment()->where('talk_duration_seconds', '<=', $shortCallThresholdSeconds))->latest('hung_up_at')->get();
        $noAnswerIncidents = AppointmentConsultantNoAnswer::where('appointment_id', $appointment->id)->whereHas('callLog', fn ($query) => $query->duringAppointment())->latest('no_answer_at')->get();
        $callbackAvailable = ConsultationAccess::schemaReady(['appointment_callback_requests']);
        $callbackRequests = $callbackAvailable
            ? AppointmentCallbackRequest::with(['requester', 'callLog'])->where('appointment_id', $appointment->id)->latest('requested_at')->get()
            : collect();
        $stats['callback_requests'] = $callbackRequests->count();
        $billing = $billingService->ensure($appointment);
        $billing?->load(['approver', 'walletTransaction', 'audits.actor', 'adjustments.actor']);
        $consultationCase = $caseService->ensure($appointment);
        $consultationCase->load(['reports.author', 'events.actor', 'completedBy', 'reopenedBy', 'noteAuthor']);
        $previousConsultationReports = AppointmentConsultationReport::with(['author', 'appointment.doctor'])
            ->whereHas('appointment', fn ($query) => $query->where('user_id', $appointment->user_id)->where('status', '<>', AppointmentUserStatusEnum::STATUS_CANCEL->value))
            ->where('appointment_id', '<>', $appointment->id)
            ->latest()
            ->limit(50)
            ->get();
        $outcomes = AppointmentConsultationReport::OUTCOMES;
        $canEditCase = ! $appointment->trashed()
            && ((int) request()->user()->id === (int) $appointment->doctor_id || request()->user()->can('SUPER_ADMIN'));
        $callbackAvailability = $callbackService->availability($appointment);
        $showCallbackAction = $canEditCase
            && $appointment->kind === \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP
            && $appointment->status !== AppointmentUserStatusEnum::STATUS_CANCEL
            && $consultationCase->state === 'OPEN';
        $canRequestCallback = $callbackAvailable && $canEditCase
            && $appointment->kind === \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP
            && $appointment->status !== AppointmentUserStatusEnum::STATUS_CANCEL
            && $consultationCase->state === 'OPEN'
            && $callbackAvailability['available'];

        return view('onlineconsultation::call-reports.appointment', compact('appointment', 'calls', 'hangupIncidents', 'noAnswerIncidents', 'callbackRequests', 'stats', 'billing', 'consultationCase', 'previousConsultationReports', 'outcomes', 'canEditCase', 'canRequestCallback', 'showCallbackAction', 'callbackAvailability', 'callbackAvailable', 'alternatePhonesAvailable', 'shortCallThresholdSeconds'));
    }

    public function updateAppointmentTime(Request $request, AppointmentUser $appointment)
    {
        abort_if($appointment->trashed(), 404);
        $this->authorize('update', $appointment);

        $data = $request->validate([
            'date' => ['required', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'send_sms' => ['nullable', 'boolean'],
        ], [
            'date.required' => 'تاریخ نوبت را انتخاب کنید.',
            'start_time.required' => 'ساعت شروع را وارد کنید.',
            'start_time.date_format' => 'فرمت ساعت شروع صحیح نیست.',
            'end_time.required' => 'ساعت پایان را وارد کنید.',
            'end_time.date_format' => 'فرمت ساعت پایان صحیح نیست.',
            'end_time.after' => 'ساعت پایان باید بعد از ساعت شروع باشد.',
        ]);

        try {
            $date = \Verta::parse($data['date'])->toCarbon();
        } catch (\Throwable) {
            throw ValidationException::withMessages(['date' => 'تاریخ انتخاب‌شده معتبر نیست.']);
        }

        [$hour, $minute] = array_map('intval', explode(':', $data['start_time']));
        $oldDate = $appointment->date_visit->copy();

        DB::transaction(function () use ($appointment, $date, $hour, $minute, $data): void {
            $appointment->update([
                'date_visit' => Carbon::instance($date)->setTime($hour, $minute)->toDateTimeString(),
                'start_time' => $data['start_time'].':00',
                'end_time' => $data['end_time'].':00',
            ]);
        });

        $appointment->refresh();
        $appointment->setting?->runGenerateCacheJob(specialDayConvert($oldDate));
        if (! $appointment->date_visit->isSameDay($oldDate)) {
            $appointment->setting?->runGenerateCacheJob(specialDayConvert($appointment->date_visit));
        }

        $smsQueued = false;
        if ($request->boolean('send_sms')) {
            $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_TIME_UPDATE);
            if (filled($smsTemplate) && filled($appointment->user?->mobile)) {
                $appointment->notify(new AppointmentSmsNotification($smsTemplate));
                $smsQueued = true;
            }
        }

        $message = match (true) {
            $smsQueued => 'زمان نوبت با موفقیت تغییر کرد و پیامک تغییر زمان در صف ارسال قرار گرفت.',
            $request->boolean('send_sms') => 'زمان نوبت تغییر کرد؛ اما قالب پیامک یا شماره موبایل بیمار در دسترس نبود.',
            default => 'زمان نوبت با موفقیت تغییر کرد.',
        };

        return back()->with('success', $message);
    }

    public function call(AppointmentCallLog $callLog)
    {
        $shortCallThresholdSeconds = max(0, (int) ConsultationSetting::current()->ignored_short_call_minutes) * 60;
        $callLog->load(['appointment.user', 'appointment.doctor', 'operator', 'consultantHangup', 'consultantNoAnswer']);
        return view('onlineconsultation::call-reports.call', compact('callLog', 'shortCallThresholdSeconds'));
    }

    public function approveBilling(Request $request, AppointmentBillingRecord $billingRecord, AppointmentBillingService $service)
    {
        $data = $request->validate([
            'approved_unused_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);
        $service->confirmAndRefund($billingRecord, (int) $data['approved_unused_minutes'], $request->user(), $data['reason'] ?? null);
        return back()->with('success', 'محاسبه نهایی تأیید، مبلغ به کیف پول بیمار واریز و رکورد مالی قفل شد.');
    }

    public function correctBilling(Request $request, AppointmentBillingRecord $billingRecord, AppointmentBillingService $service)
    {
        abort_unless($request->user()?->can('SUPER_ADMIN'), 403, 'فقط مدیر کل امکان ثبت اصلاح مالی پس از تسویه را دارد.');
        $data = $request->validate([
            'corrected_unused_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'reason' => ['required', 'string', 'max:2000'],
            'request_token' => ['required', 'uuid'],
        ]);
        $service->correctCompletedRefund($billingRecord, (int) $data['corrected_unused_minutes'], $request->user(), $data['reason'], $data['request_token']);
        return back()->with('success', 'اصلاح مالی به‌صورت یک عملیات مستقل در کیف پول و سابقه حسابرسی ثبت شد.');
    }
}
