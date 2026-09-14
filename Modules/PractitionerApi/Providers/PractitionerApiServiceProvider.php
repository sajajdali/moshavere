<?php

namespace Modules\PractitionerApi\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\PractitionerApi\Http\Middleware\AuthenticatePractitionerApi;

class PractitionerApiServiceProvider extends ServiceProvider
{
    private const MODULE_NAME = 'PractitionerApi';

    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path(self::MODULE_NAME, 'config/config.php'),
            'practitionerapi'
        );

        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            module_path(self::MODULE_NAME, 'Resources/views'),
            'practitionerapi',
        );

        $this->loadMigrationsFrom(
            module_path(self::MODULE_NAME, 'database/migrations')
        );

        $this->registerRateLimiters();

        Scramble::registerApi('practitioner', [
            'api_path' => config('practitionerapi.path', 'api/practitioner/v1'),
            'info' => [
                'title' => 'Practitioner Mobile API',
                'version' => config('practitionerapi.version', '1.0.0'),
                'description' => <<<'MARKDOWN'
API رسمی اپلیکیشن Android و iOS پزشکان و مشاوران.

### Tenant
تمام درخواست‌ها باید به دامنه Tenant مرکز درمانی ارسال شوند. مسیر ثابت نسخه اول
`/api/practitioner/v1` است. توکن، پزشک و داده‌های یک Tenant در Tenant دیگر معتبر
نیستند و دامنه مرکزی اجازه استفاده از این API را ندارد.

### ورود و Sanctum
ابتدا `auth/otp/request` و سپس `auth/otp/verify` را فراخوانی کنید. مقدار
`access_token` پاسخ ورود را به‌شکل `Authorization: Bearer {token}` ارسال کنید.
توکن 90 روز اعتبار دارد. ورود دوباره با همان `device_identifier` توکن قبلی همان
نصب را باطل می‌کند؛ سایر دستگاه‌ها فعال می‌مانند. `auth/logout` فقط دستگاه جاری
را خارج می‌کند و refresh token وجود ندارد.

### OTP و محدودیت‌ها
OTP چهاررقمی، دارای اعتبار 120 ثانیه و فاصله ارسال مجدد 60 ثانیه است. پس از پنج
کد اشتباه، ورود 15 دقیقه قفل می‌شود. محدودیت route برای ارسال، 3 درخواست در دقیقه
برای هر IP و 5 درخواست در ساعت برای هر شماره است. محدودیت بررسی، 10 درخواست در
دقیقه برای هر IP و 5 درخواست در دقیقه برای هر شماره است. پاسخ‌های 423 و 429 در
صورت امکان هدر `Retry-After` و فیلد `retry_after` برحسب ثانیه دارند.

اگر مدیر Tenant گزینه «ورود تستی» را فعال کند، پزشک یا مشاور فعال همان Tenant بدون
نیاز به مجوز شخصی `app_access` و بدون ارسال پیامک با کد ثابت `1234` وارد می‌شود.
فقط در همین حالت پاسخ درخواست و تأیید کد شامل `test_mode: true` و
`test_code: "1234"` است. در حالت خاموش این فیلدها اصلاً در پاسخ وجود ندارند.

### پاسخ و خطا
پاسخ تکی در `data` و فهرست صفحه‌بندی‌شده در `data`, `links`, `meta` قرار می‌گیرد.
خطاهای Laravel دارای `message` هستند و خطای validation علاوه بر آن `errors` با
آرایه پیام‌های هر فیلد دارد. کلاینت باید رفتار را بر اساس HTTP status و نام فیلد
انجام دهد و متن فارسی پیام را مبنای منطق برنامه قرار ندهد.

### ثبت Softphone و SIP
پس از ورود موفق، آبجکت `data.practitioner.softphone` و پس از هر بار بازشدن اپ،
آبجکت `data.softphone` در `GET /me` منبع قطعی ثبت Softphone است. `server_address`،
`server_host`، `server_port` و `transport` از تنظیمات ثابت VoIP همان Tenant خوانده
می‌شوند؛ `extension`، `username` و `password` مختص پزشک/مشاور واردشده هستند.
آدرس قدیمی احتمالی روی پروفایل پزشک مبنا نیست. کلاینت فقط وقتی
`configured=true` است باید SIP REGISTER انجام دهد؛ در غیر این صورت
`missing_fields` فیلدهای ناقص را مشخص می‌کند. `server_address` مقدار خام ثبت‌شده در
پنل و `server_host` نام میزبان نرمال‌شده برای SDK سافت‌فون است. `server_port` پورت
SIP است و از port موجود در URL مربوط به درخواست تماس استخراج نمی‌شود.

`password` اطلاعات محرمانه است: فقط روی HTTPS دریافت شود، در log، analytics، crash
report، notification یا storage معمولی نوشته نشود و صرفاً در حافظه یا secure
storage سیستم‌عامل برای راه‌اندازی Softphone نگهداری شود. دریافت مجدد تنظیمات پس از
بازشدن اپ با `GET /me` انجام می‌شود تا تغییرات سرور و حساب پزشک اعمال شوند.

### قرارداد مشترک
زمان‌ها ISO-8601 همراه offset، مدت تماس برحسب ثانیه، مدت مالی برحسب دقیقه و مبلغ
به‌صورت integer و برحسب تومان است. تاریخ شمسی فقط فیلد نمایشی است. قواعد مجاز بودن
عملیات در `actions` از سرور می‌آیند و نباید در کلاینت تکرار شوند.

فهرست‌های بزرگ از pagination استاندارد Laravel با `data`, `links`, `meta` استفاده
می‌کنند. جزئیات filter و sort مجاز در همان endpoint مستند می‌شود. برای `429` و
`423` فقط مطابق `Retry-After` تلاش مجدد کنید. endpointهای مالی/حساس در فاز مربوط
هدر `Idempotency-Key` را مستند می‌کنند؛ کلاینت در retry همان کلید را تکرار می‌کند.
MARKDOWN,
            ],
            'ui' => [
                'title' => 'Practitioner Mobile API',
            ],
            'renderer' => 'elements',
            'renderers' => [
                'elements' => [
                    'view' => 'practitionerapi::docs',
                    'theme' => 'light',
                    'hideTryIt' => false,
                    'hideSchemas' => false,
                    'tryItCredentialsPolicy' => 'include',
                    'layout' => 'responsive',
                    'router' => 'hash',
                ],
            ],
            'security_strategy' => [
                MiddlewareAuthSecurityStrategy::class,
                [
                    'middleware' => [AuthenticatePractitionerApi::class],
                ],
            ],
        ])->expose(
            ui: 'docs/practitioner',
            document: 'docs/practitioner/openapi.json',
        );
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('practitioner-otp-request', static function (Request $request): array {
            $mobile = preg_replace('/\D+/', '', convert2english((string) $request->input('mobile')));

            return [
                Limit::perMinute(3)->by('otp-request-ip:'.$request->ip()),
                Limit::perHour(5)->by('otp-request-mobile:'.$mobile),
            ];
        });

        RateLimiter::for('practitioner-otp-verify', static function (Request $request): array {
            $mobile = preg_replace('/\D+/', '', convert2english((string) $request->input('mobile')));

            return [
                Limit::perMinute(10)->by('otp-verify-ip:'.$request->ip()),
                Limit::perMinute(5)->by('otp-verify-mobile:'.$mobile),
            ];
        });
    }
}
