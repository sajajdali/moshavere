<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\OnlineConsultation\Models\Consultation;
use Modules\OnlineConsultation\Models\ConsultationCall;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;

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

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'booking_enabled' => 'required|boolean', 'app_enabled' => 'required|boolean',
            'timezone' => 'required|timezone',
            'connection_method' => ['required', Rule::in(['operator', 'callback', 'app'])],
            'voip_driver' => ['required', Rule::in(['unconfigured', 'asterisk', 'issabel', 'freepbx', 'other'])],
            'voip_host' => ['nullable', 'string', 'max:253', 'regex:/^[a-zA-Z0-9.\-:]+$/'],
            'voip_port' => 'required|integer|min:1|max:65535',
            'voip_transport' => ['required', Rule::in(['tls', 'tcp', 'udp'])],
            'voip_username' => 'nullable|string|max:255',
            'voip_secret' => 'nullable|string|max:1024',
            'clear_voip_secret' => 'sometimes|boolean',
            'outbound_caller_id' => ['nullable', 'regex:/^\+?[0-9]{3,20}$/'],
            'queue_number' => ['nullable', 'regex:/^[0-9]{1,20}$/'],
            'ring_timeout_seconds' => 'required|integer|min:10|max:180',
            'max_attempts' => 'required|integer|min:1|max:5',
            'allow_transfer' => 'required|boolean',
            'recording_requested' => 'required|boolean',
            'consent_required' => 'required|boolean|required_if:recording_requested,1|accepted_if:recording_requested,1',
            'patient_instructions' => 'nullable|string|max:3000',
        ], [], [
            'voip_host' => 'آدرس سرور ویپ',
            'consent_required' => 'رضایت برای ضبط',
        ]);
        if ($request->boolean('clear_voip_secret')) {
            $data['voip_secret'] = null;
        } elseif (! $request->filled('voip_secret')) {
            unset($data['voip_secret']);
        }
        unset($data['clear_voip_secret']);
        ConsultationSetting::current()->update($data);

        return redirect()->route('admin.consultation.settings')->with('success', 'تنظیمات مشاوره آنلاین ذخیره شد.');
    }
}
