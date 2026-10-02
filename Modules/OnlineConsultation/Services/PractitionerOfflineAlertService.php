<?php

namespace Modules\OnlineConsultation\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\OnlineConsultation\Models\PractitionerOfflineAlert;

class PractitionerOfflineAlertService
{
    public function __construct(private PractitionerExtensionStatusService $extensionStatus) {}

    public function dispatchDue(): void
    {
        $settings = ConsultationSetting::current();
        if (! $settings->offline_alert_enabled || blank($settings->offline_alert_api_url) || blank($settings->offline_alert_route)) return;

        $targetMinute = now()->addMinutes(3);

        AppointmentUser::with(['doctor.tokens', 'doctor.consultationPractitioner'])
            ->where('kind', AppointmentUserKindEnum::VOIP->value)
            ->where('status', AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value)
            ->whereBetween('date_visit', [$targetMinute->copy()->startOfMinute(), $targetMinute->copy()->endOfMinute()])
            ->whereNotIn('id', PractitionerOfflineAlert::query()->select('appointment_id'))
            ->chunkById(100, function ($appointments) use ($settings): void {
                foreach ($appointments as $appointment) $this->process($appointment, $settings);
            });
    }

    private function process(AppointmentUser $appointment, ConsultationSetting $settings): void
    {
        $profile = $appointment->doctor?->consultationPractitioner;
        if (! $profile || ! $profile->active || ! $profile->app_access || blank($profile->extension) || blank($appointment->doctor?->mobile)) return;
        if (! $this->hasActiveAppToken($appointment->doctor)) return;

        $check = $this->extensionStatus->check($profile);
        if ($check['state'] === 'online') return;

        $requestId = 'offline-alert-appointment-'.$appointment->id;
        $alert = PractitionerOfflineAlert::firstOrCreate(['appointment_id' => $appointment->id], [
            'practitioner_id' => $profile->id,
            'request_id' => $requestId,
            'extension' => $profile->extension,
            'phone' => $appointment->doctor->mobile,
            'route_name' => $settings->offline_alert_route,
            'endpoint' => $settings->offline_alert_api_url,
            'status' => $check['state'] === 'offline' ? 'sending' : 'check_failed',
            'error_message' => $check['state'] === 'offline' ? null : $check['detail'],
            'checked_at' => now(),
        ]);
        if (! $alert->wasRecentlyCreated || $check['state'] !== 'offline') return;

        $payload = [
            'phone' => $alert->phone,
            'extension' => $alert->extension,
            'route' => $alert->route_name,
            'appointment_id' => $appointment->id,
            'request_id' => $requestId,
            'minutes_until' => 2,
        ];
        $alert->update(['request_payload' => $payload]);
        $started = microtime(true);
        try {
            $response = Http::withHeaders(['X-Call-Fire-Key' => (string) $settings->voip_call_token])
                ->acceptJson()->asJson()->withoutRedirecting()->connectTimeout(5)->timeout(20)
                ->post($alert->endpoint, $payload);
            $accepted = $response->successful();
            $alert->update([
                'status' => $accepted ? 'accepted' : 'failed',
                'http_status' => $response->status(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'response_payload' => is_array($response->json()) ? $response->json() : null,
                'response_body' => Str::limit($response->body(), 10000, ''),
                'error_message' => $accepted ? null : 'API تماس هشدار وضعیت HTTP '.$response->status().' برگرداند.',
                'sent_at' => $accepted ? now() : null,
            ]);
        } catch (ConnectionException $exception) {
            $alert->update(['status' => 'failed', 'duration_ms' => (int) round((microtime(true) - $started) * 1000), 'error_message' => Str::limit($exception->getMessage(), 2000, '')]);
        } catch (\Throwable $exception) {
            report($exception);
            $alert->update(['status' => 'failed', 'duration_ms' => (int) round((microtime(true) - $started) * 1000), 'error_message' => Str::limit($exception->getMessage(), 2000, '')]);
        }
    }

    private function hasActiveAppToken($user): bool
    {
        return $user->tokens()->whereJsonContains('abilities', config('practitionerapi.token_ability', 'practitioner-app'))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->exists();
    }
}
