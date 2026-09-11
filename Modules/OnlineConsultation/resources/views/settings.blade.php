@extends('onlineconsultation::shell')
@section('consultation-title', 'تنظیمات مشاوره آنلاین')
@section('consultation-description', 'تنظیم نوبت‌دهی، دسترسی اپلیکیشن و اتصال ویپ')
@section('consultation-content')
<div class="oc-notice"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p>درخواست تماس مشاور به مسیر ثابت <bdi class="oc-ltr">/api/v1/VoIP/request_call</bdi> روی آدرس سرور VoIP ارسال می‌شود و فقط پاسخ HTTP 202 موفق است.</p></div>
@php($record = $settings)
<form method="POST" action="{{ route('admin.consultation.settings.save') }}" class="oc-stack">
    @csrf @method('PUT')
    <section class="oc-panel" id="voip-settings">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-toggle-on" aria-hidden="true"></i>دسترسی و روش مشاوره</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::checkbox', ['name' => 'booking_enabled', 'label' => 'فعال‌بودن رزرو مشاوره', 'help' => 'تنظیم رزرو برای زمان راه‌اندازی سرویس'])
        @include('onlineconsultation::checkbox', ['name' => 'app_enabled', 'label' => 'دسترسی به اپلیکیشن', 'help' => 'هر همکار به مجوز اختصاصی در پروفایل خود نیز نیاز دارد.'])
        @include('onlineconsultation::field', ['name' => 'connection_method', 'label' => 'روش برقراری تماس', 'options' => ['operator' => 'با هماهنگی اپراتور', 'callback' => 'تماس با دو طرف از سرور', 'app' => 'از اپلیکیشن'], 'required' => true])
        @include('onlineconsultation::field', ['name' => 'timezone', 'label' => 'منطقه زمانی', 'direction' => 'ltr', 'help' => 'مثال: Asia/Tehran', 'required' => true])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-network-wired" aria-hidden="true"></i>تنظیمات VoIP</h2><p class="oc-subtitle">آدرس این بخش مرجع همه مشاوران سایت است و در فرم پزشک قابل ویرایش نیست.</p></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'voip_driver', 'label' => 'نوع سرور', 'options' => ['unconfigured' => 'هنوز مشخص نشده', 'asterisk' => 'Asterisk', 'issabel' => 'Issabel', 'freepbx' => 'FreePBX', 'other' => 'سایر'], 'required' => true])
        @include('onlineconsultation::field', ['name' => 'voip_host', 'label' => 'آدرس سرور', 'direction' => 'ltr', 'help' => 'نمونه: http://rokhvanak.ir:2214 — مسیر /api/v1/VoIP/request_call خودکار اضافه می‌شود.'])
        @include('onlineconsultation::field', ['name' => 'voip_port', 'label' => 'پورت SIP', 'type' => 'number', 'min' => 1, 'max' => 65535, 'required' => true])
        @include('onlineconsultation::field', ['name' => 'voip_transport', 'label' => 'پروتکل انتقال', 'options' => ['tls' => 'TLS', 'tcp' => 'TCP', 'udp' => 'UDP'], 'required' => true])
        @include('onlineconsultation::field', ['name' => 'voip_username', 'label' => 'نام کاربری اتصال', 'direction' => 'ltr'])
        @include('onlineconsultation::field', ['name' => 'voip_secret', 'label' => 'رمز اتصال', 'type' => 'password', 'help' => 'برای حفظ رمز قبلی، خالی بگذارید.'])
        <div class="oc-secret oc-full"><span class="oc-help">رمز ذخیره‌شده: {{ $settings->getRawOriginal('voip_secret') ? 'دارد' : 'ندارد' }}</span><label class="oc-check" for="clear_voip_secret"><input class="oc-check-input" id="clear_voip_secret" type="checkbox" name="clear_voip_secret" value="1" @checked(old('clear_voip_secret'))><span class="oc-check-title">حذف رمز ذخیره‌شده</span></label></div>
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-phone-volume" aria-hidden="true"></i>مدیریت تماس و ضبط</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'outbound_caller_id', 'label' => 'شماره نمایش تماس خروجی', 'direction' => 'ltr'])
        @include('onlineconsultation::field', ['name' => 'queue_number', 'label' => 'شماره صف', 'direction' => 'ltr'])
        @include('onlineconsultation::field', ['name' => 'ring_timeout_seconds', 'label' => 'مهلت زنگ‌خوردن (ثانیه)', 'type' => 'number', 'min' => 10, 'max' => 180, 'required' => true])
        @include('onlineconsultation::field', ['name' => 'max_attempts', 'label' => 'حداکثر تلاش تماس', 'type' => 'number', 'min' => 1, 'max' => 5, 'required' => true])
        @include('onlineconsultation::field', ['name' => 'ignored_short_call_minutes', 'label' => 'حد تماس کوتاه / حداقل مکالمه معتبر (دقیقه)', 'type' => 'number', 'min' => 0, 'max' => 30, 'required' => true, 'help' => 'مدت کمتر یا مساوی این حد، تماس کوتاه است و قطع مشاور هشدار محسوب می‌شود؛ مدت بیشتر از آن، مشاوره انجام‌شده و پایان عادی تماس است. این تماس‌های کوتاه از زمان مالی نیز حذف می‌شوند. پیش‌فرض: ۶ دقیقه.'])
        @include('onlineconsultation::checkbox', ['name' => 'allow_transfer', 'label' => 'اجازه انتقال تماس'])
        @include('onlineconsultation::checkbox', ['name' => 'recording_requested', 'label' => 'درخواست ضبط مکالمه', 'help' => 'پس از اتصال سرویس و دریافت رضایت طرفین'])
        @include('onlineconsultation::checkbox', ['name' => 'consent_required', 'label' => 'الزام رضایت طرفین برای ضبط'])
        </div></div>
    </section>
    <div class="oc-savebar"><p>تغییرات با انتخاب دکمه ذخیره اعمال می‌شوند.</p><div class="oc-actions"><a class="oc-btn" href="{{ route('admin.consultation.dashboard') }}">بازگشت</a><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i>ذخیره تنظیمات</button></div></div>
</form>
@endsection
