<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\OnlineConsultation\Models\Consultation;
use Modules\OnlineConsultation\Models\ConsultationCall;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Models\AppointmentBillingRecord;
use Modules\OnlineConsultation\Services\AppointmentBillingService;

class ConsultationController extends Controller
{
    public function dashboard()
    {
        $settings = ConsultationSetting::current();
        $localStart = now($settings->timezone)->startOfDay();
        $start = $localStart->copy()->setTimezone(config('app.timezone'));
        $end = $localStart->copy()->addDay()->setTimezone(config('app.timezone'));

        return view('onlineconsultation::dashboard', [
            'settings' => $settings,
            'patientNoShowCount' => \Modules\OnlineConsultation\Models\AppointmentConsultationCase::where('state', 'PATIENT_NO_SHOW')->whereHas('appointment', fn ($q) => $q->whereBetween('date_visit', [$start, $end->copy()->subSecond()])->where('status', '<>', \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL->value))->count(),
            'todayCount' => Consultation::whereBetween('scheduled_at', [$start, $end->copy()->subSecond()])->count(),
            'readyCount' => ConsultationPractitioner::where('active', true)->where('availability', 'ready')->count(),
            'activeCalls' => ConsultationCall::whereIn('status', ['ringing', 'answered'])->count(),
            'missedCalls' => ConsultationCall::where('status', 'missed')->whereBetween('created_at', [$start, $end->copy()->subSecond()])->count(),
            'consultations' => Consultation::with('practitioner')->latest('scheduled_at')->paginate(15),
        ]);
    }

    public function settings()
    {
        return view('onlineconsultation::settings', ['settings' => ConsultationSetting::current()]);
    }

    public function saveSettings(Request $request, AppointmentBillingService $billingService)
    {
        $settings = ConsultationSetting::current();
        $request->merge([
            'call_center_number' => $this->normalizePhoneNumber($request->input('call_center_number')),
        ]);
        $data = $request->validate([
            'booking_enabled' => 'required|boolean', 'app_enabled' => 'required|boolean',
            'test_login_enabled' => 'required|boolean',
            'timezone' => 'required|timezone',
            'connection_method' => ['required', Rule::in(['operator', 'callback', 'app'])],
            'voip_driver' => ['required', Rule::in(['unconfigured', 'asterisk', 'issabel', 'freepbx', 'other'])],
            'voip_host' => ['nullable', 'url:http,https', 'max:255'],
            'softphone_server_address' => ['nullable', 'string', 'max:255', 'regex:/^\S+$/'],
            'voip_port' => 'required|integer|min:1|max:65535',
            'voip_transport' => ['required', Rule::in(['tls', 'tcp', 'udp'])],
            'voip_call_token' => [
                'nullable',
                Rule::requiredIf(fn () => $request->filled('voip_host')
                    && ! $request->boolean('clear_voip_call_token')
                    && ! $settings->getRawOriginal('voip_call_token')),
                'string',
                'max:1024',
            ],
            'offline_alert_enabled' => 'required|boolean',
            'offline_alert_api_url' => ['nullable', Rule::requiredIf(fn () => $request->boolean('offline_alert_enabled')), 'url:http,https', 'max:255'],
            'offline_alert_route' => ['nullable', Rule::requiredIf(fn () => $request->boolean('offline_alert_enabled')), 'string', 'max:255', 'regex:/^[A-Za-z0-9._\-\/]+$/'],
            'clear_voip_call_token' => 'sometimes|boolean',
            'call_center_number' => ['nullable', 'regex:/^\+?[0-9]{3,20}$/'],
            'outbound_caller_id' => ['nullable', 'regex:/^\+?[0-9]{3,20}$/'],
            'queue_number' => ['nullable', 'regex:/^[0-9]{1,20}$/'],
            'ring_timeout_seconds' => 'required|integer|min:10|max:180',
            'max_attempts' => 'required|integer|min:1|max:5',
            'ignored_short_call_minutes' => 'required|integer|min:0|max:30',
            'connection_overhead_minutes' => 'required|integer|min:0|max:60',
            'allow_transfer' => 'required|boolean',
            'recording_requested' => 'required|boolean',
            'consent_required' => 'required|boolean|required_if:recording_requested,1|accepted_if:recording_requested,1',
            'patient_instructions' => 'nullable|string|max:3000',
        ], [
            'voip_call_token.required' => 'برای آدرس سرور VoIP، توکن تماس اتوماتیک الزامی است.',
        ], [
            'voip_host' => 'آدرس سرور ویپ',
            'offline_alert_api_url' => 'آدرس API تماس هشدار با مشاور',
            'offline_alert_route' => 'نام Route پیام صوتی آنلاین‌شدن پزشک',
            'softphone_server_address' => 'آدرس سرور Softphone',
            'call_center_number' => 'شماره مرکز تماس',
            'consent_required' => 'رضایت برای ضبط',
        ]);
        if ($request->boolean('clear_voip_call_token')) {
            $data['voip_call_token'] = null;
        } elseif (! $request->filled('voip_call_token')) {
            unset($data['voip_call_token']);
        }
        unset($data['clear_voip_call_token']);
        $thresholdChanged = (int) $settings->ignored_short_call_minutes !== (int) $data['ignored_short_call_minutes'];
        $settings->update($data);
        if ($thresholdChanged) {
            AppointmentBillingRecord::query()->chunkById(100, function ($records) use ($billingService) {
                foreach ($records as $record) $billingService->refresh($record);
            });
        }

        return redirect()->route('admin.consultation.settings')->with('success', 'تنظیمات مشاوره آنلاین ذخیره شد.');
    }

    private function normalizePhoneNumber(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') return null;

        return strtr(preg_replace('/[\s\-()]+/', '', trim($value)), [
            '۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4',
            '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
        ]);
    }

}
