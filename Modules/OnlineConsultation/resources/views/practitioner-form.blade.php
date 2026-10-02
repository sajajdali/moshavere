@extends('onlineconsultation::shell')
@section('consultation-title', $person->exists ? 'ویرایش پزشک / کارشناس' : 'افزودن پزشک / کارشناس')
@section('consultation-description', 'مدیریت حساب متصل، دسترسی‌ها و داخلی')
@section('consultation-header-actions')
<a class="oc-btn" href="{{ route('admin.consultation.practitioners') }}"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>بازگشت به فهرست</a>
@endsection
@section('consultation-content')
<div class="oc-stack">
@if(!$person->exists)
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>انتخاب حساب کاربری</h2><p class="oc-subtitle">پزشک یا کارشناس را از کاربران موجود انتخاب کنید.</p></div></div>
        <div class="oc-panel-body">
            <div class="oc-toolbar">
                <form method="GET" action="{{ route('admin.consultation.practitioners.create') }}" class="oc-search">
                    <div class="oc-field"><label for="account" class="oc-label">شماره موبایل</label><input class="oc-input" name="account" id="account" value="{{ $account }}" placeholder="حداقل ۳ رقم موبایل" dir="ltr" inputmode="tel" minlength="3" required></div>
                    <button class="oc-btn" type="submit">جست‌وجو</button>
                </form>
                <a class="oc-btn" href="{{ route('admin.user.create') }}" target="_blank" rel="noopener"><i class="fa-solid fa-user-plus" aria-hidden="true"></i>ساخت حساب جدید</a>
            </div>
            @if($account !== '')
                <div class="oc-results">
                    @forelse($accounts as $candidate)
                        <a class="oc-btn" href="{{ route('admin.consultation.practitioners.create', ['user' => $candidate->id]) }}">{{ $candidate->fullName ?: 'کاربر '.$candidate->id }} <bdi>{{ $candidate->mobile }}</bdi></a>
                    @empty
                        <p class="oc-help">حسابی با این شماره پیدا نشد. شماره دیگری جست‌وجو کنید یا حساب جدید بسازید.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </section>
@endif
@php($record = $person)
@if($person->exists)
    <section class="oc-extension-status is-loading" id="practitioner-extension-status" data-url="{{ route('admin.consultation.practitioners.extension-status', $person) }}">
        <div class="oc-extension-status-orbit"><span></span><i class="fa-solid fa-phone-volume" aria-hidden="true"></i></div>
        <div class="oc-extension-status-copy">
            <small>وضعیت زنده داخلی <bdi class="oc-ltr">{{ $person->extension ?: '—' }}</bdi></small>
            <h2 data-status-label>در حال بررسی اتصال پزشک…</h2>
            <p data-status-detail>وضعیت مستقیماً از سرور VoIP دریافت می‌شود.</p>
            <span data-status-time><i class="fa-regular fa-clock"></i> در حال اتصال</span>
        </div>
        <button class="oc-btn oc-extension-refresh" type="button" data-status-refresh><i class="fa-solid fa-rotate"></i><span>بررسی مجدد</span></button>
    </section>
@endif
<form method="POST" action="{{ $person->exists ? route('admin.consultation.practitioners.update', $person) : route('admin.consultation.practitioners.store') }}" class="oc-stack">
    @csrf @if($person->exists) @method('PUT') @endif
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i>مشخصات پزشک / کارشناس</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @if($selectedUser)
            <div class="oc-profile-management oc-full">
                <div class="oc-profile-management-person">
                    <span class="oc-profile-management-avatar"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i></span>
                    <div>
                        <small>حساب متصل به مشاوره</small>
                        <strong>{{ $selectedUser->fullName ?: 'کاربر '.$selectedUser->id }}</strong>
                        <bdi class="oc-ltr">{{ $selectedUser->mobile ?: 'بدون شماره موبایل' }}</bdi>
                    </div>
                </div>
                <div class="oc-profile-shortcuts" aria-label="دسترسی‌های ویرایش پزشک">
                    <a href="{{ route('admin.user.edit', $selectedUser) }}">
                        <span class="oc-profile-shortcut-icon"><i class="fa-solid fa-address-card" aria-hidden="true"></i></span>
                        <span><strong>ویرایش اطلاعات پزشک</strong><small>نام، موبایل و اطلاعات اصلی حساب</small></span>
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('admin.doctor.info', $selectedUser) }}">
                        <span class="oc-profile-shortcut-icon"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
                        <span><strong>پرونده و اطلاعات تکمیلی</strong><small>تخصص، معرفی و اطلاعات پروفایل پزشک</small></span>
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        @endif
        <div class="oc-field"><label for="user_id" class="oc-label">شناسه حساب کاربری<span class="oc-required" aria-hidden="true">*</span></label><input class="oc-input" name="user_id" id="user_id" type="number" dir="ltr" min="1" value="{{ old('user_id', $person->user_id) }}" @readonly($person->exists) required aria-invalid="{{ $errors->has('user_id') ? 'true' : 'false' }}" aria-describedby="user_id-hint"><div id="user_id-hint"><small class="oc-help">{{ $person->exists ? 'حساب متصل به این پروفایل قابل تغییر نیست.' : 'با انتخاب نتیجه جست‌وجو، شناسه حساب اینجا قرار می‌گیرد.' }}</small>@error('user_id')<small class="oc-error">{{ $message }}</small>@enderror</div></div>
        @include('onlineconsultation::field', ['name' => 'display_name', 'label' => 'نام نمایشی', 'required' => true])
        @include('onlineconsultation::field', ['name' => 'kind', 'label' => 'نوع همکار', 'options' => ['doctor' => 'پزشک', 'expert' => 'کارشناس'], 'required' => true])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-user-shield" aria-hidden="true"></i>دسترسی و وضعیت پاسخ‌گویی</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::checkbox', ['name' => 'active', 'label' => 'همکار فعال است', 'help' => 'غیرفعال‌کردن همکار، دسترسی اپلیکیشن او را هم قطع می‌کند.'])
        @include('onlineconsultation::checkbox', ['name' => 'app_access', 'label' => 'دسترسی به اپلیکیشن مشاوره', 'help' => 'فعال‌بودن دسترسی کلی اپلیکیشن در تنظیمات سایت نیز لازم است.'])
        @include('onlineconsultation::checkbox', ['name' => 'tomorrow_schedule_sms_enabled', 'label' => 'پیامک برنامه نوبت‌های فردا', 'help' => 'در صورت فعال‌بودن تنظیم کلی، هر شب ساعت تعیین‌شده فقط زمانی پیامک ارسال می‌شود که این همکار برای فردا نوبت تأییدشده داشته باشد.'])
        @include('onlineconsultation::field', ['name' => 'availability', 'label' => 'وضعیت پاسخ‌گویی', 'options' => ['offline' => 'آفلاین', 'ready' => 'آماده', 'busy' => 'مشغول'], 'help' => 'وضعیت فعلاً به‌صورت دستی تعیین می‌شود.', 'required' => true])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-phone" aria-hidden="true"></i>داخلی و تعرفه</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'extension', 'label' => 'شماره داخلی', 'direction' => 'ltr', 'help' => 'داخلی باید در این سایت یکتا باشد.'])
        @include('onlineconsultation::field', ['name' => 'sip_username', 'label' => 'نام کاربری SIP', 'direction' => 'ltr', 'help' => 'نام کاربری اختصاصی همین پزشک؛ پس از ورود و در پروفایل برای ثبت Softphone ارسال می‌شود.'])
        @include('onlineconsultation::field', ['name' => 'sip_secret', 'label' => 'رمز SIP', 'type' => 'password', 'help' => 'رمز اختصاصی همین پزشک؛ برای حفظ رمز قبلی خالی بگذارید. API آن را فقط پس از احراز هویت به اپ تحویل می‌دهد.'])
        <div class="oc-secret"><span class="oc-help">رمز ذخیره‌شده: {{ $person->getRawOriginal('sip_secret') ? 'دارد' : 'ندارد' }}</span><label class="oc-check" for="clear_sip_secret"><input class="oc-check-input" id="clear_sip_secret" type="checkbox" name="clear_sip_secret" value="1" @checked(old('clear_sip_secret'))><span class="oc-check-title">حذف رمز ذخیره‌شده</span></label></div>
        @include('onlineconsultation::field', ['name' => 'payout_hourly_rate', 'label' => 'هزینه ساعتی مشاور (تومان)', 'type' => 'number', 'min' => 0, 'max' => 1000000000, 'help' => 'تنها نرخ مالی قابل تنظیم برای مشاور است و برای محاسبه سهم او از زمان مؤثر مشاوره استفاده می‌شود. مبلغ بیمار مستقیماً از مبلغ همان نوبت خوانده می‌شود.'])
        @include('onlineconsultation::field', ['name' => 'duration_minutes', 'label' => 'مدت اختصاصی جلسه (دقیقه)', 'type' => 'number', 'min' => 5, 'max' => 180, 'help' => 'خالی بگذارید تا مدت پیش‌فرض سایت استفاده شود.'])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-note-sticky" aria-hidden="true"></i>یادداشت داخلی</h2></div></div>
        <div class="oc-panel-body"><label for="notes" class="oc-label">یادداشت داخلی</label><textarea id="notes" class="oc-input" name="notes" rows="3" maxlength="3000">{{ old('notes', $person->notes) }}</textarea>@error('notes')<small class="oc-error">{{ $message }}</small>@enderror</div>
    </section>
    <div class="oc-savebar"><p>اطلاعات و دسترسی‌ها را بررسی و ذخیره کنید.</p><div class="oc-actions"><a class="oc-btn" href="{{ route('admin.consultation.practitioners') }}">انصراف</a><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i>ذخیره اطلاعات</button></div></div>
</form>
</div>
@endsection
@if($person->exists)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const card = document.getElementById('practitioner-extension-status');
    if (!card) return;
    const button = card.querySelector('[data-status-refresh]');
    const label = card.querySelector('[data-status-label]');
    const detail = card.querySelector('[data-status-detail]');
    const time = card.querySelector('[data-status-time]');
    const states = ['is-loading', 'is-online', 'is-offline', 'is-unavailable', 'is-unconfigured'];
    const icons = {online: 'fa-phone-volume', offline: 'fa-phone-slash', unavailable: 'fa-triangle-exclamation', unconfigured: 'fa-gear'};

    async function refreshStatus() {
        card.classList.remove(...states);
        card.classList.add('is-loading');
        button.disabled = true;
        button.querySelector('i').classList.add('fa-spin');
        label.textContent = 'در حال بررسی اتصال پزشک…';
        detail.textContent = 'درخواست وضعیت داخلی به سرور VoIP ارسال شد.';
        try {
            const response = await fetch(card.dataset.url, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'});
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const result = await response.json();
            const state = ['online', 'offline', 'unavailable', 'unconfigured'].includes(result.state) ? result.state : 'unavailable';
            card.classList.remove(...states);
            card.classList.add('is-' + state);
            card.querySelector('.oc-extension-status-orbit i').className = 'fa-solid ' + (icons[state] || icons.unavailable);
            label.textContent = result.label;
            detail.textContent = result.detail;
            time.innerHTML = '<i class="fa-regular fa-clock"></i> آخرین بررسی: <bdi>' + result.checked_at + '</bdi>';
        } catch (error) {
            card.classList.remove(...states);
            card.classList.add('is-unavailable');
            card.querySelector('.oc-extension-status-orbit i').className = 'fa-solid fa-triangle-exclamation';
            label.textContent = 'امکان بررسی وضعیت وجود ندارد';
            detail.textContent = 'ارتباط صفحه با سرویس بررسی وضعیت برقرار نشد.';
            time.innerHTML = '<i class="fa-regular fa-clock"></i> بررسی ناموفق';
        } finally {
            button.disabled = false;
            button.querySelector('i').classList.remove('fa-spin');
        }
    }

    button.addEventListener('click', refreshStatus);
    refreshStatus();
});
</script>
@endpush
@endif
