<?php

namespace Modules\OnlineConsultation\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\OnlineConsultation\Models\AppointmentCallbackRequest;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\User\Entities\User;
use RuntimeException;

class ConsultationCallbackService
{
    public const PATIENT_CALL_GRACE_MINUTES = 5;

    public function availability(AppointmentUser $appointment): array
    {
        if (! $appointment->date_visit || ! $appointment->start_time) {
            return ['available' => false, 'available_at' => null, 'reason' => 'زمان شروع نوبت مشخص نیست.'];
        }

        $start = Carbon::parse($appointment->date_visit, 'Asia/Tehran')
            ->setTimeFromTimeString($appointment->start_time);
        $availableAt = $start->copy()->addMinutes(self::PATIENT_CALL_GRACE_MINUTES);

        if (now('Asia/Tehran')->lt($availableAt)) {
            return [
                'available' => false,
                'available_at' => $availableAt,
                'reason' => 'اگر بیمار تا ۵ دقیقه پس از شروع نوبت تماس نگیرد، تماس با بیمار فعال می‌شود.',
            ];
        }

        if ($this->patientHasCalledSinceStart($appointment, $start)) {
            return [
                'available' => false,
                'available_at' => $availableAt,
                'reason' => 'بیمار پس از شروع نوبت تماس گرفته است؛ تماس خودکار با بیمار فعال نیست.',
            ];
        }

        return ['available' => true, 'available_at' => $availableAt, 'reason' => null];
    }

    public function request(AppointmentUser $appointment, ConsultationPractitioner $practitioner, User $actor): AppointmentCallbackRequest
    {
        $availability = $this->availability($appointment);
        if (! $availability['available']) {
            throw new RuntimeException($availability['reason']);
        }

        $settings = ConsultationSetting::current();
        $endpoint = $this->callbackEndpoint((string) $settings->voip_host);
        $phone = $this->normalizeIranianMobile((string) $appointment->user?->mobile);
        $extension = trim((string) $practitioner->extension);

        $callback = DB::transaction(function () use ($appointment, $actor, $endpoint, $extension, $phone) {
            AppointmentUser::withTrashed()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $sequence = AppointmentCallbackRequest::where('appointment_id', $appointment->id)->count() + 1;
            $requestId = "appointment-{$appointment->id}-callback-{$sequence}";
            $payload = [
                'appointment_id' => $appointment->id,
                'patient_phone' => $phone,
                'advisor_extension' => $extension,
                'request_id' => $requestId,
                'requested_by' => 'consultant',
            ];

            return AppointmentCallbackRequest::create([
                'appointment_id' => $appointment->id,
                'request_id' => $requestId,
                'requested_by_id' => $actor->id,
                'requested_by_role' => 'consultant',
                'patient_phone' => $phone,
                'advisor_extension' => $extension,
                'endpoint' => $endpoint,
                'status' => AppointmentCallbackRequest::STATUS_PENDING,
                'request_payload' => $payload,
                'requested_at' => now(),
            ]);
        }, 3);

        if (! filter_var($endpoint, FILTER_VALIDATE_URL) || ! in_array(parse_url($endpoint, PHP_URL_SCHEME), ['http', 'https'], true)) {
            $this->recordRejected($callback, 'آدرس کامل HTTP یا HTTPS سرور درخواست تماس در تنظیمات VoIP ثبت نشده است.');
        }
        if (! preg_match('/^09\d{9}$/', $phone)) {
            $this->recordRejected($callback, 'شماره موبایل بیمار برای درخواست تماس معتبر نیست.');
        }
        if ($extension === '') {
            $this->recordRejected($callback, 'داخلی مشاور تعریف نشده است.');
        }

        $started = microtime(true);
        try {
            $request = Http::acceptJson()->asJson()->withoutRedirecting()->connectTimeout(5)->timeout(20);
            if (filled($settings->voip_username) || filled($settings->voip_secret)) {
                $request = $request->withBasicAuth((string) $settings->voip_username, (string) $settings->voip_secret);
            }
            $response = $request->post($endpoint, $callback->request_payload);
            $body = Str::limit($response->body(), 10000, '');
            $accepted = $response->status() === 202;
            $callback->update([
                'status' => $accepted ? AppointmentCallbackRequest::STATUS_ACCEPTED : AppointmentCallbackRequest::STATUS_FAILED,
                'http_status' => $response->status(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'response_payload' => is_array($response->json()) ? $response->json() : null,
                'response_body' => $body !== '' ? $body : null,
                'error_message' => $accepted ? null : 'سرور تماس وضعیت HTTP '.$response->status().' برگرداند؛ فقط پاسخ HTTP 202 به معنی ثبت درخواست تماس است.',
                'completed_at' => now(),
            ]);
        } catch (ConnectionException $exception) {
            $this->recordFailure($callback, $started, $exception);
        } catch (\Throwable $exception) {
            $this->recordFailure($callback, $started, $exception);
            report($exception);
        }

        $callback->refresh();
        if ($callback->status !== AppointmentCallbackRequest::STATUS_ACCEPTED) {
            throw new RuntimeException($callback->error_message ?: 'ارسال درخواست تماس ناموفق بود.');
        }

        return $callback;
    }

    private function recordFailure(AppointmentCallbackRequest $callback, float $started, \Throwable $exception): void
    {
        $callback->update([
            'status' => AppointmentCallbackRequest::STATUS_FAILED,
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            'error_message' => Str::limit($exception->getMessage(), 2000, ''),
            'completed_at' => now(),
        ]);
    }

    private function recordRejected(AppointmentCallbackRequest $callback, string $message): never
    {
        $callback->update([
            'status' => AppointmentCallbackRequest::STATUS_FAILED,
            'error_message' => $message,
            'completed_at' => now(),
        ]);

        throw new RuntimeException($message);
    }

    private function normalizeIranianMobile(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0098')) $digits = substr($digits, 4);
        elseif (str_starts_with($digits, '98') && strlen($digits) === 12) $digits = substr($digits, 2);
        if (strlen($digits) === 10 && str_starts_with($digits, '9')) $digits = '0'.$digits;

        return $digits;
    }

    private function callbackEndpoint(string $address): string
    {
        $address = trim($address);
        if (! filter_var($address, FILTER_VALIDATE_URL)) return $address;

        $parts = parse_url($address);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');
        if (! in_array($scheme, ['http', 'https'], true) || $host === '') return $address;

        if (str_contains($host, ':') && ! str_starts_with($host, '[')) $host = '['.$host.']';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port.'/api/v1/VoIP/request_call';
    }

    private function patientHasCalledSinceStart(AppointmentUser $appointment, Carbon $start): bool
    {
        $now = now('Asia/Tehran');
        $patientDirection = fn ($query) => $query->whereNull('direction')->orWhere('direction', '<>', 'OUTBOUND');

        $linkedCallExists = $appointment->callLogs()
            ->where($patientDirection)
            ->where(function ($query) use ($start, $now) {
                $query->whereBetween('call_entered_at', [$start, $now])
                    ->orWhere(function ($query) {
                        $query->whereNull('call_entered_at')->where('appointment_state', 'IN_APPOINTMENT_TIME');
                    });
            })->exists();

        if ($linkedCallExists) return true;

        $phone = $this->normalizeIranianMobile((string) $appointment->user?->mobile);
        if (! preg_match('/^09\d{9}$/', $phone)) return false;
        $phoneSuffix = substr($phone, -10);

        return \Modules\OnlineConsultation\Models\AppointmentCallLog::whereNull('appointment_id')
            ->where($patientDirection)
            ->whereBetween('call_entered_at', [$start, $now])
            ->get(['patient_phone'])
            ->contains(fn ($call) => substr($this->normalizeIranianMobile((string) $call->patient_phone), -10) === $phoneSuffix);
    }
}
