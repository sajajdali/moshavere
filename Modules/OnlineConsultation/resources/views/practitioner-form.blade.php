@extends('onlineconsultation::shell')
@section('consultation-title', $person->exists ? 'ویرایش پزشک / کارشناس' : 'افزودن پزشک / کارشناس')
@section('consultation-description', 'مدیریت حساب متصل، دسترسی‌ها، داخلی و برنامه حضور')
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
<form method="POST" action="{{ $person->exists ? route('admin.consultation.practitioners.update', $person) : route('admin.consultation.practitioners.store') }}" class="oc-stack">
    @csrf @if($person->exists) @method('PUT') @endif
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-user-doctor" aria-hidden="true"></i>مشخصات پزشک / کارشناس</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @if($selectedUser)<div class="oc-selected-account oc-full"><strong>حساب انتخاب‌شده:</strong> {{ $selectedUser->fullName }} <bdi>{{ $selectedUser->mobile }}</bdi></div>@endif
        <div class="oc-field"><label for="user_id" class="oc-label">شناسه حساب کاربری<span class="oc-required" aria-hidden="true">*</span></label><input class="oc-input" name="user_id" id="user_id" type="number" dir="ltr" min="1" value="{{ old('user_id', $person->user_id) }}" @readonly($person->exists) required aria-invalid="{{ $errors->has('user_id') ? 'true' : 'false' }}" aria-describedby="user_id-hint"><div id="user_id-hint"><small class="oc-help">{{ $person->exists ? 'حساب متصل به این پروفایل قابل تغییر نیست.' : 'با انتخاب نتیجه جست‌وجو، شناسه حساب اینجا قرار می‌گیرد.' }}</small>@error('user_id')<small class="oc-error">{{ $message }}</small>@enderror</div></div>
        @include('onlineconsultation::field', ['name' => 'display_name', 'label' => 'نام نمایشی', 'required' => true])
        @include('onlineconsultation::field', ['name' => 'kind', 'label' => 'نوع همکار', 'options' => ['doctor' => 'پزشک', 'expert' => 'کارشناس'], 'required' => true])
        @include('onlineconsultation::field', ['name' => 'specialty', 'label' => 'تخصص / حوزه مشاوره'])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-user-shield" aria-hidden="true"></i>دسترسی و وضعیت پاسخ‌گویی</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::checkbox', ['name' => 'active', 'label' => 'همکار فعال است', 'help' => 'غیرفعال‌کردن همکار، دسترسی اپلیکیشن او را هم قطع می‌کند.'])
        @include('onlineconsultation::checkbox', ['name' => 'app_access', 'label' => 'دسترسی به اپلیکیشن مشاوره', 'help' => 'فعال‌بودن دسترسی کلی اپلیکیشن در تنظیمات سایت نیز لازم است.'])
        @include('onlineconsultation::field', ['name' => 'availability', 'label' => 'وضعیت پاسخ‌گویی', 'options' => ['offline' => 'آفلاین', 'ready' => 'آماده', 'busy' => 'مشغول'], 'help' => 'وضعیت فعلاً به‌صورت دستی تعیین می‌شود.', 'required' => true])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-phone" aria-hidden="true"></i>داخلی و تعرفه</h2></div></div>
        <div class="oc-panel-body"><div class="oc-grid">
        @include('onlineconsultation::field', ['name' => 'extension', 'label' => 'شماره داخلی', 'direction' => 'ltr', 'help' => 'داخلی باید در این سایت یکتا باشد.'])
        @include('onlineconsultation::field', ['name' => 'sip_username', 'label' => 'نام کاربری SIP', 'direction' => 'ltr'])
        @include('onlineconsultation::field', ['name' => 'sip_secret', 'label' => 'رمز SIP', 'type' => 'password', 'help' => 'برای حفظ رمز قبلی، خالی بگذارید.'])
        <div class="oc-secret"><span class="oc-help">رمز ذخیره‌شده: {{ $person->getRawOriginal('sip_secret') ? 'دارد' : 'ندارد' }}</span><label class="oc-check" for="clear_sip_secret"><input class="oc-check-input" id="clear_sip_secret" type="checkbox" name="clear_sip_secret" value="1" @checked(old('clear_sip_secret'))><span class="oc-check-title">حذف رمز ذخیره‌شده</span></label></div>
        @include('onlineconsultation::field', ['name' => 'fee', 'label' => 'تعرفه اختصاصی (تومان)', 'type' => 'number', 'min' => 0, 'max' => 1000000000, 'help' => 'خالی بگذارید تا تعرفه پیش‌فرض سایت استفاده شود.'])
        @include('onlineconsultation::field', ['name' => 'duration_minutes', 'label' => 'مدت اختصاصی جلسه (دقیقه)', 'type' => 'number', 'min' => 5, 'max' => 180, 'help' => 'خالی بگذارید تا مدت پیش‌فرض سایت استفاده شود.'])
        </div></div>
    </section>
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i>برنامه هفتگی</h2><p class="oc-subtitle">برای هر روز یک بازه حضور، بر اساس منطقه زمانی تنظیمات مشاوره تعریف کنید.</p></div></div>
        <div class="oc-table-wrap" role="region" aria-label="برنامه هفتگی قابل پیمایش" tabindex="0">
            <table class="oc-table oc-schedule"><thead><tr><th scope="col">روز هفته</th><th scope="col">وضعیت حضور</th><th scope="col">از ساعت</th><th scope="col">تا ساعت</th></tr></thead><tbody>
                @foreach($days as $index => $day)
                    <tr>
                        <td>{{ $day }}</td>
                        <td><input type="hidden" name="weekly_schedule[{{ $index }}][enabled]" value="0"><label><input class="oc-check-input" type="checkbox" aria-label="فعال بودن {{ $day }}" name="weekly_schedule[{{ $index }}][enabled]" value="1" @checked(old("weekly_schedule.$index.enabled", $person->weekly_schedule[$index]['enabled'] ?? false))><span>فعال</span></label></td>
                        <td><input class="oc-input" aria-label="شروع {{ $day }}" type="time" name="weekly_schedule[{{ $index }}][start]" value="{{ old("weekly_schedule.$index.start", $person->weekly_schedule[$index]['start'] ?? '09:00') }}" aria-invalid="{{ $errors->has("weekly_schedule.$index.start") ? 'true' : 'false' }}">@error("weekly_schedule.$index.start")<small class="oc-error">{{ $message }}</small>@enderror</td>
                        <td><input class="oc-input" aria-label="پایان {{ $day }}" type="time" name="weekly_schedule[{{ $index }}][end]" value="{{ old("weekly_schedule.$index.end", $person->weekly_schedule[$index]['end'] ?? '17:00') }}" aria-invalid="{{ $errors->has("weekly_schedule.$index.end") ? 'true' : 'false' }}">@error("weekly_schedule.$index.end")<small class="oc-error">{{ $message }}</small>@enderror</td>
                    </tr>
                @endforeach
            </tbody></table>
        </div>
        <div class="oc-panel-body"><label for="notes" class="oc-label">یادداشت داخلی</label><textarea id="notes" class="oc-input" name="notes" rows="3" maxlength="3000">{{ old('notes', $person->notes) }}</textarea>@error('notes')<small class="oc-error">{{ $message }}</small>@enderror</div>
    </section>
    <div class="oc-savebar"><p>اطلاعات و برنامه حضور را بررسی و ذخیره کنید.</p><div class="oc-actions"><a class="oc-btn" href="{{ route('admin.consultation.practitioners') }}">انصراف</a><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i>ذخیره اطلاعات</button></div></div>
</form>
</div>
@endsection
