<?php

namespace Modules\OnlineConsultation\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;

class PractitionerExtensionStatusService
{
    public function check(ConsultationPractitioner $practitioner): array
    {
        $extension = trim((string) $practitioner->extension);
        $settings = ConsultationSetting::current();
        $host = rtrim(trim((string) $settings->voip_host), '/');
        $token = trim((string) $settings->voip_call_token);

        if ($extension === '') {
            return $this->result('unconfigured', 'داخلی تعریف نشده', 'ابتدا شماره داخلی پزشک را ثبت کنید.', $extension);
        }
        if (! filter_var($host, FILTER_VALIDATE_URL) || ! in_array(parse_url($host, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return $this->result('unconfigured', 'سرور VoIP تنظیم نشده', 'آدرس معتبر سرور VoIP را در تنظیمات مشاوره ثبت کنید.', $extension);
        }
        if ($token === '') {
            return $this->result('unconfigured', 'کلید اتصال تنظیم نشده', 'کلید Call Fire را در تنظیمات مشاوره ثبت کنید.', $extension);
        }

        try {
            $response = Http::withHeaders(['X-Call-Fire-Key' => $token])
                ->acceptJson()->withoutRedirecting()->connectTimeout(4)->timeout(8)
                ->get($host.'/api/v1/VoIP/extension_status', ['extension' => $extension]);

            if (! $response->successful()) {
                return $this->result('unavailable', 'خطا در دریافت وضعیت', 'سرور VoIP پاسخ HTTP '.$response->status().' برگرداند.', $extension, $response->status());
            }

            $payload = is_array($response->json()) ? $response->json() : [];
            $rawStatus = data_get($payload, 'data.status')
                ?? data_get($payload, 'status')
                ?? data_get($payload, 'data.state')
                ?? data_get($payload, 'state');
            $onlineValue = data_get($payload, 'data.online')
                ?? data_get($payload, 'online')
                ?? data_get($payload, 'data.registered')
                ?? data_get($payload, 'registered');
            $normalized = strtolower(trim((string) $rawStatus));
            $online = is_bool($onlineValue)
                ? $onlineValue
                : (is_numeric($onlineValue)
                    ? (bool) $onlineValue
                    : in_array(strtolower(trim((string) $onlineValue)), ['true', 'yes', 'online', 'registered'], true));

            if ($online || in_array($normalized, ['online', 'registered', 'available', 'ready', 'idle', 'busy', 'inuse', 'in-use'], true)) {
                $detail = in_array($normalized, ['busy', 'inuse', 'in-use'], true)
                    ? 'داخلی روی سرور ثبت است و اکنون در حال مکالمه است.'
                    : 'داخلی روی سرور VoIP ثبت و در دسترس است.';

                return $this->result('online', 'پزشک هم‌اکنون آنلاین می‌باشد', $detail, $extension, $response->status());
            }

            if ($onlineValue === false || $onlineValue === 0 || in_array($normalized, ['offline', 'unregistered', 'unavailable', 'unknown', 'not_registered', 'not-registered'], true)) {
                return $this->result('offline', 'پزشک آفلاین است', 'داخلی روی سرور VoIP ثبت یا در دسترس نیست.', $extension, $response->status());
            }

            return $this->result('unavailable', 'پاسخ وضعیت نامشخص است', 'سرور پاسخ داد، اما وضعیت آنلاین یا آفلاین در پاسخ مشخص نبود.', $extension, $response->status());
        } catch (ConnectionException) {
            return $this->result('unavailable', 'سرور VoIP در دسترس نیست', 'اتصال به سرور وضعیت داخلی برقرار نشد؛ چند لحظه دیگر دوباره بررسی کنید.', $extension);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->result('unavailable', 'بررسی وضعیت ناموفق بود', 'هنگام دریافت وضعیت داخلی خطایی رخ داد.', $extension);
        }
    }

    private function result(string $state, string $label, string $detail, string $extension, ?int $httpStatus = null): array
    {
        return compact('state', 'label', 'detail', 'extension', 'httpStatus') + [
            'checked_at' => now('Asia/Tehran')->format('H:i:s'),
        ];
    }
}
