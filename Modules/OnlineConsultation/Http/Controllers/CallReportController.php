<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentCallLog;
use Modules\OnlineConsultation\Models\AppointmentBillingRecord;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Services\AppointmentBillingService;

class CallReportController extends Controller
{
    public function index(Request $request)
    {
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
            ->selectRaw("SUM(CASE WHEN final_result <> 'ANSWERED' THEN 1 ELSE 0 END) as unanswered_count")
            ->selectRaw('SUM(talk_duration_seconds) as talk_seconds, SUM(wait_duration_seconds) as wait_seconds')
            ->selectRaw('MAX(call_entered_at) as last_call_at')
            ->groupBy('appointment_id')
            ->orderByDesc('last_call_at')
            ->paginate(20)
            ->withQueryString();

        $appointmentModels = AppointmentUser::with(['user', 'doctor'])
            ->whereIn('id', $appointments->getCollection()->pluck('appointment_id'))
            ->get()->keyBy('id');
        $appointments->getCollection()->transform(function ($row) use ($appointmentModels) {
            $row->appointment = $appointmentModels->get($row->appointment_id);
            return $row;
        });

        $unlinkedCalls = (clone $base)->whereNull('appointment_id')->latest('call_entered_at')->limit(10)->get();

        $practitioners = ConsultationPractitioner::with('user')->where('active', true)
            ->whereNotNull('extension')->orderBy('display_name')->get();

        return view('onlineconsultation::call-reports.index', compact('appointments', 'unlinkedCalls', 'stats', 'filters', 'practitioners'));
    }

    public function appointment(AppointmentUser $appointment, AppointmentBillingService $billingService)
    {
        $appointment->load(['user', 'doctor']);
        $query = AppointmentCallLog::where('appointment_id', $appointment->id);
        $stats = [
            'calls' => (clone $query)->count(),
            'answered' => (clone $query)->where('final_result', 'ANSWERED')->count(),
            'unanswered' => (clone $query)->where('final_result', '<>', 'ANSWERED')->count(),
            'talk_seconds' => (int) (clone $query)->sum('talk_duration_seconds'),
            'wait_seconds' => (int) (clone $query)->sum('wait_duration_seconds'),
            'average_talk_seconds' => (int) round((float) ((clone $query)->where('final_result', 'ANSWERED')->avg('talk_duration_seconds') ?? 0)),
            'last_call_at' => (clone $query)->max('call_entered_at'),
        ];
        $calls = $query->with('operator')->latest('call_entered_at')->paginate(20);
        $billing = $billingService->ensure($appointment);
        $billing?->load(['approver', 'walletTransaction', 'audits.actor', 'adjustments.actor']);

        return view('onlineconsultation::call-reports.appointment', compact('appointment', 'calls', 'stats', 'billing'));
    }

    public function call(AppointmentCallLog $callLog)
    {
        $callLog->load(['appointment.user', 'appointment.doctor', 'operator']);
        return view('onlineconsultation::call-reports.call', compact('callLog'));
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
