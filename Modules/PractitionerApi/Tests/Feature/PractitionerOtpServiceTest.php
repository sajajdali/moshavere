<?php

namespace Modules\PractitionerApi\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Api\Notifications\AuthSmsNotification;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Modules\PractitionerApi\Models\PractitionerOtpRequest;
use Modules\PractitionerApi\Services\PractitionerOtpService;
use Modules\PractitionerApi\Http\Controllers\V1\Auth\LogoutController;
use Modules\User\Entities\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PractitionerOtpServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('user_metas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->timestamps();
        });
        Schema::create('consultation_practitioners', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique();
            $table->string('display_name');
            $table->string('kind', 20);
            $table->string('specialty')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('app_access')->default(false);
            $table->string('availability', 20)->default('offline');
            $table->string('extension')->nullable();
            $table->string('sip_username')->nullable();
            $table->text('sip_secret')->nullable();
            $table->json('weekly_schedule')->nullable();
            $table->timestamps();
        });
        Schema::create('consultation_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('test_login_enabled')->default(false);
            $table->string('voip_host')->nullable();
            $table->unsignedSmallInteger('voip_port')->default(5061);
            $table->string('voip_transport')->default('tls');
            $table->timestamps();
        });
        ConsultationSetting::query()->create([
            'id' => 1,
            'test_login_enabled' => false,
            'voip_host' => 'https://voip.example.test:2214',
            'voip_port' => 5061,
            'voip_transport' => 'tls',
        ]);
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('access_token_id');
            $table->string('type');
            $table->text('fcm_token')->nullable();
            $table->string('device_version')->nullable();
            $table->json('device_info')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        (require module_path('PractitionerApi', 'database/migrations/tenant/2026_09_13_100000_create_practitioner_otp_requests_table.php'))->up();
        (require module_path('PractitionerApi', 'database/migrations/tenant/2026_09_13_110000_add_lock_to_practitioner_otp_requests_table.php'))->up();
    }

    public function test_active_practitioner_can_receive_and_verify_login_otp(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner();
        $service = app(PractitionerOtpService::class);

        $requestResult = $service->request($practitioner->user->mobile, 'test-installation', '127.0.0.1');
        $this->assertSame(['test_mode' => false], $requestResult);

        $otpRequest = PractitionerOtpRequest::query()->firstOrFail();
        $code = null;
        Notification::assertSentTo(
            $otpRequest,
            AuthSmsNotification::class,
            function (AuthSmsNotification $notification) use (&$code): bool {
                $code = $notification->code;

                return true;
            }
        );

        $result = $service->verify($practitioner->user->mobile, $code, [
            'device_name' => 'Test iPhone',
            'device_os' => 'ios',
            'device_identifier' => 'test-installation',
            'device_info' => ['model' => 'iPhone'],
        ]);

        $token = PersonalAccessToken::findToken($result['token']);
        $this->assertNotNull($token);
        $this->assertTrue($token->can('practitioner-app'));
        $this->assertTrue($token->expires_at->between(now()->addDays(89), now()->addDays(91)));
        $this->assertNotNull($otpRequest->fresh()->consumed_at);
        $this->assertTrue($result['practitioner']['softphone']['configured']);
        $this->assertSame('voip.example.test', $result['practitioner']['softphone']['server_host']);
        $this->assertSame('sip-test-user', $result['practitioner']['softphone']['username']);
        $this->assertSame('private-sip-password', $result['practitioner']['softphone']['password']);
        $this->assertDatabaseHas('user_devices', [
            'user_id' => $practitioner->user_id,
            'access_token_id' => $token->id,
            'type' => 'ios',
            'device_version' => null,
        ]);

        $this->expectException(ValidationException::class);
        $service->verify($practitioner->user->mobile, $code, [
            'device_name' => 'Test iPhone',
            'device_os' => 'ios',
        ]);
    }

    public function test_enabled_test_login_uses_1234_without_sms_and_bypasses_personal_app_access(): void
    {
        Notification::fake();
        ConsultationSetting::query()->whereKey(1)->update(['test_login_enabled' => true]);
        $practitioner = $this->practitioner(['app_access' => false]);
        $service = app(PractitionerOtpService::class);

        $requestResult = $service->request($practitioner->user->mobile, 'test-mode-installation', '127.0.0.1');

        $this->assertSame(['test_mode' => true, 'test_code' => '1234'], $requestResult);
        Notification::assertNothingSent();
        $this->assertTrue(Hash::check('1234', PractitionerOtpRequest::query()->firstOrFail()->code_hash));

        $result = $service->verify(
            $practitioner->user->mobile,
            '1234',
            $this->device('test-mode-installation'),
        );

        $this->assertTrue($result['test_mode']);
        $this->assertSame('1234', $result['test_code']);
        $this->assertNotNull(PersonalAccessToken::findToken($result['token']));
    }

    public function test_disabled_test_login_neither_accepts_1234_nor_exposes_test_code(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner();
        $service = app(PractitionerOtpService::class);
        $requestResult = $service->request($practitioner->user->mobile, null, '127.0.0.1');
        PractitionerOtpRequest::query()->update(['code_hash' => Hash::make('9876')]);

        $this->assertSame(['test_mode' => false], $requestResult);
        $this->assertArrayNotHasKey('test_code', $requestResult);

        try {
            $service->verify($practitioner->user->mobile, '1234', $this->device());
            $this->fail('Test code was accepted while test login was disabled.');
        } catch (ValidationException) {
            $this->assertSame(1, PractitionerOtpRequest::query()->firstOrFail()->attempts);
        }
    }

    public function test_inactive_or_app_disabled_practitioner_cannot_request_otp(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner(['app_access' => false]);

        try {
            app(PractitionerOtpService::class)->request($practitioner->user->mobile, null, '127.0.0.1');
            $this->fail('Inactive practitioner received an OTP.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        Notification::assertNothingSent();
        $this->assertDatabaseCount('practitioner_otp_requests', 0);
    }

    public function test_inactive_practitioner_cannot_request_otp(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner(['active' => false]);

        $this->expectException(HttpException::class);
        app(PractitionerOtpService::class)->request($practitioner->user->mobile, null, '127.0.0.1');
    }

    public function test_expired_otp_cannot_be_verified(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner();
        $service = app(PractitionerOtpService::class);
        $code = $this->requestAndCaptureCode($service, $practitioner, 'expired-installation');
        PractitionerOtpRequest::query()->update(['expires_at' => now()->subSecond()]);

        $this->expectException(ValidationException::class);
        $service->verify($practitioner->user->mobile, $code, $this->device('expired-installation'));
    }

    public function test_resend_is_rejected_during_cooldown_with_retry_after(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner();
        $service = app(PractitionerOtpService::class);

        $service->request($practitioner->user->mobile, 'installation-1', '127.0.0.1');

        try {
            $service->request($practitioner->user->mobile, 'installation-1', '127.0.0.1');
            $this->fail('OTP resend was not rate limited.');
        } catch (HttpResponseException $exception) {
            $this->assertSame(429, $exception->getResponse()->getStatusCode());
            $this->assertNotNull($exception->getResponse()->headers->get('Retry-After'));
        }
    }

    public function test_fifth_invalid_code_locks_otp_for_fifteen_minutes(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner();
        $service = app(PractitionerOtpService::class);
        $service->request($practitioner->user->mobile, null, '127.0.0.1');
        PractitionerOtpRequest::query()->update(['code_hash' => Hash::make('1234')]);

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            try {
                $service->verify($practitioner->user->mobile, '9999', $this->device());
                $this->fail('Invalid OTP was accepted.');
            } catch (ValidationException) {
                $this->assertSame($attempt, PractitionerOtpRequest::query()->firstOrFail()->attempts);
            }
        }

        try {
            $service->verify($practitioner->user->mobile, '9999', $this->device());
            $this->fail('Fifth invalid OTP did not lock login.');
        } catch (HttpResponseException $exception) {
            $this->assertSame(423, $exception->getResponse()->getStatusCode());
        }

        $otp = PractitionerOtpRequest::query()->firstOrFail();
        $this->assertSame(5, $otp->attempts);
        $this->assertNotNull($otp->locked_until);
        $this->assertNotNull($otp->consumed_at);
    }

    public function test_new_login_on_same_installation_revokes_previous_token_only(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner();
        $service = app(PractitionerOtpService::class);

        $firstCode = $this->requestAndCaptureCode($service, $practitioner, 'same-installation');
        $first = $service->verify($practitioner->user->mobile, $firstCode, $this->device('same-installation'));
        $firstTokenId = PersonalAccessToken::findToken($first['token'])->id;

        PractitionerOtpRequest::query()->update(['next_request_at' => now()->subSecond()]);
        $secondCode = $this->requestAndCaptureCode($service, $practitioner, 'same-installation');
        $second = $service->verify($practitioner->user->mobile, $secondCode, $this->device('same-installation'));

        $this->assertNull(PersonalAccessToken::query()->find($firstTokenId));
        $this->assertNotNull(PersonalAccessToken::findToken($second['token']));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseCount('user_devices', 1);
    }

    public function test_logout_revokes_current_token_and_device(): void
    {
        Notification::fake();
        $practitioner = $this->practitioner();
        $service = app(PractitionerOtpService::class);
        $code = $this->requestAndCaptureCode($service, $practitioner, 'logout-installation');
        $result = $service->verify($practitioner->user->mobile, $code, $this->device('logout-installation'));
        $token = PersonalAccessToken::findToken($result['token']);

        $request = Request::create('/api/practitioner/v1/auth/logout', 'POST');
        $request->setUserResolver(fn () => $practitioner->user->withAccessToken($token));
        $response = app(LogoutController::class)($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull(PersonalAccessToken::query()->find($token->id));
        $this->assertDatabaseCount('user_devices', 0);
    }

    /** @return array<string, mixed> */
    private function device(string $identifier = 'test-installation'): array
    {
        return [
            'device_name' => 'Test iPhone',
            'device_os' => 'ios',
            'device_identifier' => $identifier,
            'device_info' => ['model' => 'iPhone'],
        ];
    }

    private function requestAndCaptureCode(
        PractitionerOtpService $service,
        ConsultationPractitioner $practitioner,
        string $identifier,
    ): string {
        Notification::fake();
        $code = null;
        $service->request($practitioner->user->mobile, $identifier, '127.0.0.1');
        Notification::assertSentTo(
            PractitionerOtpRequest::query()->firstOrFail(),
            AuthSmsNotification::class,
            function (AuthSmsNotification $notification) use (&$code): bool {
                $code = $notification->code;

                return true;
            },
        );

        return (string) $code;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function practitioner(array $overrides = []): ConsultationPractitioner
    {
        $user = User::query()->create([
            'mobile' => '09120000001',
            'password' => 'test-password',
        ]);

        return ConsultationPractitioner::query()->create(array_replace([
            'user_id' => $user->id,
            'display_name' => 'پزشک تست',
            'kind' => 'doctor',
            'active' => true,
            'app_access' => true,
            'availability' => 'offline',
            'extension' => '102',
            'sip_username' => 'sip-test-user',
            'sip_secret' => 'private-sip-password',
        ], $overrides));
    }
}
