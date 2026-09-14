<?php

namespace Modules\PractitionerApi\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Api\Entities\UserDevice;
use Modules\Api\Notifications\AuthSmsNotification;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\PractitionerApi\Models\PractitionerOtpRequest;

class PractitionerOtpService
{
    public function __construct(private readonly PractitionerSoftphoneService $softphones)
    {
    }

    /** @return array{test_mode: bool, test_code?: string} */
    public function request(string $mobile, ?string $deviceIdentifier, ?string $ip): array
    {
        $testMode = $this->testModeEnabled();
        $this->activePractitioner($mobile, $testMode);

        $code = $testMode ? '1234' : str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $otpRequest = DB::transaction(function () use ($mobile, $deviceIdentifier, $ip, $code): PractitionerOtpRequest {
            $otpRequest = PractitionerOtpRequest::query()->lockForUpdate()->where('mobile', $mobile)->first();

            if ($otpRequest?->locked_until?->isFuture()) {
                $this->locked($otpRequest->locked_until->diffInSeconds(now()));
            }

            if ($otpRequest?->next_request_at?->isFuture()) {
                $this->tooManyRequests(
                    'برای ارسال مجدد کد کمی صبر کنید.',
                    $otpRequest->next_request_at->diffInSeconds(now()),
                );
            }

            $values = [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'ip' => $ip,
                'device_identifier' => $deviceIdentifier,
                'expires_at' => now()->addSeconds((int) config('practitionerapi.otp.ttl_seconds', 120)),
                'next_request_at' => now()->addSeconds((int) config('practitionerapi.otp.resend_after_seconds', 60)),
                'locked_until' => null,
                'consumed_at' => null,
            ];

            if ($otpRequest) {
                $values['request_count'] = $otpRequest->request_count + 1;
                $otpRequest->update($values);
            } else {
                $otpRequest = PractitionerOtpRequest::query()->create(['mobile' => $mobile] + $values);
            }

            return $otpRequest;
        });

        if (! $testMode) {
            $otpRequest->notify(new AuthSmsNotification($code));
        }

        return array_filter([
            'test_mode' => $testMode,
            'test_code' => $testMode ? $code : null,
        ], fn ($value) => $value !== null);
    }

    /**
     * @param array<string, mixed> $device
     * @return array{
     *     token: string,
     *     expires_at: string,
     *     practitioner: array{
     *         id: int,
     *         user_id: int,
     *         display_name: string,
     *         kind: string,
     *         specialty: string|null,
     *         availability: string
     *     }
     * }
     */
    public function verify(string $mobile, string $code, array $device): array
    {
        $testMode = $this->testModeEnabled();
        $result = DB::transaction(function () use ($mobile, $code, $device, $testMode): array {
            $otpRequest = PractitionerOtpRequest::query()
                ->lockForUpdate()
                ->where('mobile', $mobile)
                ->first();

            if ($otpRequest->locked_until?->isFuture()) {
                $this->locked($otpRequest->locked_until->diffInSeconds(now()));
            }

            if (! $otpRequest || $otpRequest->consumed_at || $otpRequest->expires_at->isPast()) {
                throw ValidationException::withMessages(['code' => ['کد ورود منقضی یا نامعتبر است.']]);
            }

            $maxAttempts = (int) config('practitionerapi.otp.max_attempts', 5);

            if (! Hash::check($code, $otpRequest->code_hash)) {
                $otpRequest->increment('attempts');
                if ($otpRequest->fresh()->attempts >= $maxAttempts) {
                    $lockedUntil = now()->addMinutes((int) config('practitionerapi.otp.lock_minutes', 15));
                    $otpRequest->update([
                        'locked_until' => $lockedUntil,
                        'consumed_at' => now(),
                    ]);

                    return [
                        'error' => 'locked',
                        'retry_after' => $lockedUntil->diffInSeconds(now()),
                    ];
                }

                return ['error' => 'invalid_code'];
            }

            $practitioner = $this->activePractitioner($mobile, $testMode);
            $expiresAt = now()->addDays((int) config('practitionerapi.token_expiration_days', 90));

            $this->revokePreviousTokenForInstallation(
                $practitioner->user_id,
                $device['device_identifier'] ?? null,
            );
            $this->deleteExpiredTokens($practitioner->user_id);

            $newToken = $practitioner->user->createToken(
                $device['device_name'],
                [config('practitionerapi.token_ability', 'practitioner-app')],
                $expiresAt,
            );

            /** @var PersonalAccessToken|null $tokenModel */
            $tokenModel = $newToken->accessToken;
            UserDevice::query()->create([
                'user_id' => $practitioner->user_id,
                'access_token_id' => $tokenModel->id,
                'type' => $device['device_os'],
                'fcm_token' => $device['fcm_token'] ?? null,
                'device_version' => $device['device_version'] ?? null,
                'device_info' => array_merge($device['device_info'] ?? [], array_filter([
                    'device_identifier' => $device['device_identifier'] ?? null,
                ])),
            ]);

            $otpRequest->update(['consumed_at' => now()]);

            return [
                'token' => $newToken->plainTextToken,
                'expires_at' => $expiresAt->toIso8601String(),
                'practitioner' => [
                    'id' => (int) $practitioner->id,
                    'user_id' => (int) $practitioner->user_id,
                    'display_name' => (string) $practitioner->display_name,
                    'kind' => (string) $practitioner->kind,
                    'specialty' => $practitioner->specialty,
                    'availability' => (string) $practitioner->availability,
                    'softphone' => $this->softphones->for($practitioner),
                ],
                'test_mode' => $testMode,
                'test_code' => $testMode ? '1234' : null,
            ];
        });

        if (($result['error'] ?? null) === 'locked') {
            $this->locked((int) $result['retry_after']);
        }

        if (($result['error'] ?? null) === 'invalid_code') {
            throw ValidationException::withMessages(['code' => ['کد ورود صحیح نیست.']]);
        }

        if (! $testMode) {
            unset($result['test_mode'], $result['test_code']);
        }

        return $result;
    }

    /** @return object{user: mixed, user_id: int, id: int, display_name: string, kind: string, specialty: string|null, availability: string} */
    private function activePractitioner(string $mobile, bool $testMode = false): object
    {
        $practitioner = ConsultationPractitioner::query()
            ->with('user')
            ->where('active', true)
            ->when(! $testMode, fn ($query) => $query->where('app_access', true))
            ->whereHas('user', fn ($query) => $query->where('mobile', $mobile))
            ->first();

        if (! $practitioner) {
            abort(403, 'حساب فعال پزشک یا مشاور برای این شماره یافت نشد.');
        }

        return $practitioner;
    }

    private function testModeEnabled(): bool
    {
        return (bool) ConsultationSetting::current()->test_login_enabled;
    }

    private function revokePreviousTokenForInstallation(int $userId, ?string $deviceIdentifier): void
    {
        if (! $deviceIdentifier) {
            return;
        }

        $devices = UserDevice::query()
            ->where('user_id', $userId)
            ->where('device_info->device_identifier', $deviceIdentifier)
            ->get();

        foreach ($devices as $device) {
            $device->forceDelete();
            PersonalAccessToken::query()->whereKey($device->access_token_id)->delete();
        }
    }

    private function deleteExpiredTokens(int $userId): void
    {
        PersonalAccessToken::query()
            ->where('tokenable_type', config('auth.providers.users.model'))
            ->where('tokenable_id', $userId)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();
    }

    private function locked(int $retryAfter): never
    {
        throw new HttpResponseException(response()->json([
            'message' => 'ورود موقتاً به‌دلیل تلاش‌های ناموفق قفل شده است.',
            'errors' => [
                'code' => ['پس از پایان زمان قفل دوباره تلاش کنید.'],
            ],
            'retry_after' => max(1, $retryAfter),
        ], 423, ['Retry-After' => (string) max(1, $retryAfter)]));
    }

    private function tooManyRequests(string $message, int $retryAfter): never
    {
        throw new HttpResponseException(response()->json([
            'message' => $message,
            'errors' => [],
            'retry_after' => max(1, $retryAfter),
        ], 429, ['Retry-After' => (string) max(1, $retryAfter)]));
    }
}
