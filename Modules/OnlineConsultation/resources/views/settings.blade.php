@extends('onlineconsultation::shell')
@section('consultation-title', 'تنظیمات مشاوره آنلاین')
@section('consultation-description', 'تنظیم نوبت‌دهی، دسترسی اپلیکیشن و اتصال ویپ')
@section('consultation-content')
<div class="oc-notice"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p>درخواست تماس مشاور به مسیر ثابت <bdi class="oc-ltr">/api/v1/VoIP/request_call</bdi> روی آدرس سرور VoIP ارسال می‌شود و توکن با هدر <bdi class="oc-ltr">X-Call-Fire-Key</bdi> ارسال خواهد شد؛ فقط پاسخ HTTP 202 موفق است.</p></div>
@php($record = $settings)
<form method="POST" action="{{ route('admin.consultation.settings.save') }}" class="oc-stack">
    @csrf @method('PUT')
    <section class="oc-panel" id="voip-settings">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-toggle-on" aria-hidden="true"></i>دسترسی و روش مشاوره</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::checkbox', ['name' => 'booking_enabled', 'label' => 'فعال‌بودن رزرو مشاوره', 'help' => 'تنظیم رزرو برای زمان راه‌اندازی سرویس'])
        @include('onlineconsultation::checkbox', ['name' => 'app_enabled', 'label' => 'دسترسی به اپلیکیشن', 'help' => 'هر همکار به مجوز اختصاصی در پروفایل خود نیز نیاز دارد.'])
        @include('onlineconsultation::checkbox', ['name' => 'test_login_enabled', 'label' => 'ورود تستی', 'help' => 'فقط برای تست: پزشک یا مشاور فعال بدون مجوز شخصی اپ و بدون پیامک، همیشه با کد ۱۲۳۴ وارد می‌شود و کد در پاسخ API نمایش داده می‌شود.'])
        @include('onlineconsultation::field', ['name' => 'connection_method', 'label' => 'روش برقراری تماس', 'options' => ['operator' => 'با هماهنگی اپراتور', 'callback' => 'تماس با دو طرف از سرور', 'app' => 'از اپلیکیشن'], 'required' => true])
        @include('onlineconsultation::field', ['name' => 'timezone', 'label' => 'منطقه زمانی', 'direction' => 'ltr', 'help' => 'مثال: Asia/Tehran', 'required' => true])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-bell" aria-hidden="true"></i>تماس خودکار با پزشک آفلاین</h2><p class="oc-subtitle">سه دقیقه پیش از نوبت، داخلی پزشک بررسی می‌شود و اگر آفلاین باشد تماس صوتی یادآوری ارسال خواهد شد.</p></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::checkbox', ['name' => 'offline_alert_enabled', 'label' => 'فعال‌بودن هشدار تلفنی پزشک آفلاین', 'help' => 'فقط برای پزشک فعال دارای دسترسی اپلیکیشن و توکن ورود معتبر اجرا می‌شود؛ برای هر نوبت حداکثر یک تماس ثبت خواهد شد.'])
        @include('onlineconsultation::field', ['name' => 'offline_alert_api_url', 'label' => 'آدرس API تماس خودکار با مشاور', 'direction' => 'ltr', 'help' => 'آدرس کامل endpoint دریافت درخواست تماس؛ نمونه: http://server:port/api/v1/VoIP/practitioner_offline_alert'])
        @include('onlineconsultation::field', ['name' => 'offline_alert_route', 'label' => 'نام Route پیام صوتی آنلاین‌شدن پزشک', 'direction' => 'ltr', 'help' => 'نام Route تعریف‌شده روی سرور تماس که پیام «۲ دقیقه دیگر مشاوره شما فرا می‌رسد؛ لطفاً در اپلیکیشن آنلاین شوید» را پخش می‌کند. این مقدار در فیلد route درخواست ارسال می‌شود.'])
        <div class="oc-notice oc-full"><i class="fa-solid fa-circle-info"></i><p>درخواست POST با هدر <bdi class="oc-ltr">X-Call-Fire-Key</bdi> و پارامترهای <bdi class="oc-ltr">phone, extension, route, appointment_id, request_id, minutes_until</bdi> ارسال می‌شود. توکن همین بخش از «توکن تماس اتوماتیک» بالا خوانده می‌شود.</p></div>
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-network-wired" aria-hidden="true"></i>تنظیمات سرور VoIP</h2><p class="oc-subtitle">این آدرس فقط برای ارسال درخواست تماس اتوماتیک به سرور VoIP استفاده می‌شود.</p></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'voip_driver', 'label' => 'نوع سرور', 'options' => ['unconfigured' => 'هنوز مشخص نشده', 'asterisk' => 'Asterisk', 'issabel' => 'Issabel', 'freepbx' => 'FreePBX', 'other' => 'سایر'], 'required' => true])
        @include('onlineconsultation::field', ['name' => 'voip_host', 'label' => 'آدرس سرور VoIP', 'direction' => 'ltr', 'help' => 'نمونه: http://rokhvanak.ir:2214 — مسیر /api/v1/VoIP/request_call خودکار اضافه می‌شود.'])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>تنظیمات Softphone</h2><p class="oc-subtitle">این آدرس مستقل از سرور درخواست تماس است و در API ورود، پروفایل و داشبورد برای اتصال اپلیکیشن برگردانده می‌شود.</p></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'softphone_server_address', 'label' => 'آدرس سرور Softphone', 'direction' => 'ltr', 'help' => 'نام میزبان، IP یا آدرس کامل سرور SIP؛ نمونه: sip.example.com یا https://sip.example.com'])
        @include('onlineconsultation::field', ['name' => 'voip_port', 'label' => 'پورت SIP', 'type' => 'number', 'min' => 1, 'max' => 65535, 'required' => true])
        @include('onlineconsultation::field', ['name' => 'voip_transport', 'label' => 'پروتکل انتقال', 'options' => ['tls' => 'TLS', 'tcp' => 'TCP', 'udp' => 'UDP'], 'required' => true])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-key" aria-hidden="true"></i>احراز درخواست تماس VoIP</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'voip_call_token', 'label' => 'توکن تماس اتوماتیک', 'type' => 'text', 'direction' => 'ltr', 'help' => 'توکن ذخیره‌شده در هدر X-Call-Fire-Key ارسال می‌شود.'])
        <div class="oc-secret oc-full"><span class="oc-help">توکن تماس: {{ $settings->voip_call_token ? 'ثبت شده است' : 'ناقص است' }}</span><label class="oc-check" for="clear_voip_call_token"><input class="oc-check-input" id="clear_voip_call_token" type="checkbox" name="clear_voip_call_token" value="1" @checked(old('clear_voip_call_token'))><span class="oc-check-title">حذف توکن اختصاصی این بخش</span></label></div>
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i>مدیریت تماس و ضبط</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'call_center_number', 'label' => 'شماره مرکز تماس', 'direction' => 'ltr', 'help' => 'شماره‌ای که برای نمایش به کاربران، درج در پیامک‌ها و سایر بخش‌های مشاوره استفاده می‌شود؛ نمونه: 02112345678'])
        @include('onlineconsultation::field', ['name' => 'outbound_caller_id', 'label' => 'شماره نمایش تماس خروجی', 'direction' => 'ltr'])
        @include('onlineconsultation::field', ['name' => 'queue_number', 'label' => 'شماره صف', 'direction' => 'ltr'])
        @include('onlineconsultation::field', ['name' => 'ring_timeout_seconds', 'label' => 'مهلت زنگ‌خوردن (ثانیه)', 'type' => 'number', 'min' => 10, 'max' => 180, 'required' => true])
        @include('onlineconsultation::field', ['name' => 'max_attempts', 'label' => 'حداکثر تلاش تماس', 'type' => 'number', 'min' => 1, 'max' => 5, 'required' => true])
        @include('onlineconsultation::field', ['name' => 'ignored_short_call_minutes', 'label' => 'حد تشخیص تماس کوتاه (دقیقه)', 'type' => 'number', 'min' => 0, 'max' => 30, 'required' => true, 'help' => 'این مقدار فقط برای تشخیص تماس کوتاه و هشدار قطع تماس استفاده می‌شود و هیچ زمانی را از محاسبات مالی کم نمی‌کند. پیش‌فرض: ۶ دقیقه.'])
        @include('onlineconsultation::field', ['name' => 'connection_overhead_minutes', 'label' => 'زمان سربار اتصال و مکالمه (دقیقه)', 'type' => 'number', 'min' => 0, 'max' => 60, 'required' => true, 'help' => 'زمان ثابت بابت شماره‌گیری، انتظار و برقراری اتصال که به مجموع مکالمه معتبر اضافه می‌شود. مثال: با مقدار ۶، مکالمه ۳۰ دقیقه‌ای در محاسبات مالی ۳۶ دقیقه منظور می‌شود. مقدار هر نوبت هنگام ایجاد محاسبه ذخیره و ثابت می‌ماند.'])
        @include('onlineconsultation::checkbox', ['name' => 'allow_transfer', 'label' => 'اجازه انتقال تماس'])
        @include('onlineconsultation::checkbox', ['name' => 'recording_requested', 'label' => 'درخواست ضبط مکالمه', 'help' => 'پس از اتصال سرویس و دریافت رضایت طرفین'])
        @include('onlineconsultation::checkbox', ['name' => 'consent_required', 'label' => 'الزام رضایت طرفین برای ضبط'])
        </div></div>
    </section>
    <div class="oc-savebar"><p>تغییرات با انتخاب دکمه ذخیره اعمال می‌شوند.</p><div class="oc-actions"><a class="oc-btn" href="{{ route('admin.consultation.dashboard') }}">بازگشت</a><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i>ذخیره تنظیمات</button></div></div>
</form>
@endsection
