@extends('onlineconsultation::shell')
@section('consultation-title', 'پرونده مشاوره #'.$appointment->id)
@section('consultation-description', 'ثبت نتیجه، سوابق بیمار، وضعیت پرونده، تماس‌ها و محاسبات مالی این نوبت')
@section('consultation-header-actions')<a class="oc-btn" href="{{ route('admin.consultation.call-reports.index') }}"><i class="fa-solid fa-arrow-right"></i>بازگشت به گزارش‌ها</a>@endsection
@section('consultation-content')
@php
    $duration = fn ($seconds) => sprintf('%02d:%02d:%02d', intdiv((int) $seconds, 3600), intdiv(((int) $seconds) % 3600, 60), ((int) $seconds) % 60);
    $callPresentation = \Modules\OnlineConsultation\Support\CallResultPresentation::class;
    $reportDefaultNow = now('Asia/Tehran');
    $reportDefaultDate = verta($reportDefaultNow)->format('Y/m/d');
    $reportDefaultTime = $reportDefaultNow->format('H:i');
    $normalizeCallerPhone = function ($value) {
        $digits = preg_replace('/\D+/', '', convert2english((string) $value)) ?? '';
        if (str_starts_with($digits, '0098')) $digits = '0'.substr($digits, 4);
        elseif (str_starts_with($digits, '98')) $digits = '0'.substr($digits, 2);
        if (strlen($digits) === 8) $digits = '021'.$digits;
        return $digits;
    };
    $patientMobile = $normalizeCallerPhone($appointment->user?->mobile);
    $patientLandlines = $appointment->alternatePhones->pluck('phone')->map($normalizeCallerPhone)->all();
    $consultationStart = $appointment->date_visit?->copy()->timezone('Asia/Tehran');
    if ($consultationStart && filled($appointment->start_time)) {
        $consultationStart->setTimeFromTimeString($appointment->start_time);
    }
    $consultationEnd = $consultationStart?->copy();
    if ($consultationEnd && filled($appointment->end_time)) {
        $consultationEnd->setTimeFromTimeString($appointment->end_time);
        if ($consultationEnd->lte($consultationStart)) $consultationEnd->addDay();
    } elseif ($consultationEnd) {
        $consultationEnd->addMinutes(max(1, (int) ($billing?->reserved_minutes ?? 30)));
    }
@endphp
<div class="oc-stack oc-appointment-page">
    @if($appointment->trashed())<div class="oc-notice oc-notice-warning"><i class="fa-solid fa-box-archive"></i><p>این نوبت حذف شده و پرونده آن فقط برای مشاهده سوابق باز شده است؛ امکان ثبت یا تغییر گزارش وجود ندارد.</p></div>@endif
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-calendar-check"></i>مشخصات نوبت</h2>
        @if(!$appointment->trashed() && request()->user()->can('update', $appointment))
        <button type="button" class="oc-btn oc-btn-primary" data-bs-toggle="modal" data-bs-target="#quick-time-edit-modal"><i class="fa-solid fa-clock"></i>ویرایش سریع ساعت و زمان</button>
        @endif
    </div><div class="oc-panel-body oc-detail-grid">
        <div><span>بیمار</span><strong>{{ $appointment->user?->fullName ?: '—' }}</strong><small class="oc-ltr">{{ $appointment->user?->mobile ?: '—' }}</small></div>
        <div><span>پزشک / کارشناس</span><strong>{{ $appointment->doctor?->fullName ?: '—' }}</strong></div>
        <div><span>زمان نوبت</span><strong>@if($appointment->date_visit)<bdi>{{ verta($appointment->date_visit)->format('Y/m/d H:i') }}</bdi>@else—@endif</strong></div>
        <div><span>کد پیگیری</span><strong>{{ $appointment->tracking_code ?: '—' }}</strong></div>
    </div></section>

    @if($consultationStart && $consultationEnd)
    <section class="oc-consultation-clock" id="consultation-clock"
        data-start="{{ $consultationStart->getTimestampMs() }}"
        data-end="{{ $consultationEnd->getTimestampMs() }}"
        data-end-label="{{ $consultationEnd->format('H:i') }}"
        data-server-now="{{ now('Asia/Tehran')->getTimestampMs() }}">
        <div class="oc-clock-glow"></div>
        <div class="oc-clock-head">
            <div class="oc-clock-brand"><span class="oc-clock-icon"><i class="fa-solid fa-hourglass-half"></i></span><div><small>زمان‌بندی مشاوره</small><h2 id="consultation-clock-title">در حال محاسبه زمان نوبت…</h2></div></div>
            <span class="oc-clock-live" id="consultation-clock-badge"><i></i><span>در حال بررسی</span></span>
        </div>
        <div class="oc-clock-body">
            <div class="oc-clock-message"><strong id="consultation-clock-message">لطفاً چند لحظه صبر کنید</strong><span id="consultation-clock-description">زمان دقیق بر اساس ساعت رسمی سرور محاسبه می‌شود.</span></div>
            <div class="oc-countdown" id="consultation-countdown" aria-live="polite">
                <div><strong data-unit="days">۰</strong><span>روز</span></div><b>:</b>
                <div><strong data-unit="hours">۰۰</strong><span>ساعت</span></div><b>:</b>
                <div><strong data-unit="minutes">۰۰</strong><span>دقیقه</span></div><b>:</b>
                <div><strong data-unit="seconds">۰۰</strong><span>ثانیه</span></div>
            </div>
            <div class="oc-clock-ended" id="consultation-clock-ended" hidden><i class="fa-solid fa-circle-check"></i><div><small>بازه مشاوره پایان یافته است</small><strong><bdi>{{ verta($consultationEnd)->format('Y/m/d H:i') }}</bdi></strong></div></div>
        </div>
        <div class="oc-clock-progress"><span id="consultation-clock-progress"></span></div>
        <div class="oc-clock-times">
            <div><span class="oc-clock-time-icon is-start"><i class="fa-solid fa-play"></i></span><p><small>شروع مشاوره</small><strong><bdi>{{ verta($consultationStart)->format('Y/m/d') }}</bdi> · <bdi>{{ $consultationStart->format('H:i') }}</bdi></strong></p></div>
            <span class="oc-clock-route"><i class="fa-solid fa-chevron-left"></i></span>
            <div><span class="oc-clock-time-icon is-end"><i class="fa-solid fa-flag-checkered"></i></span><p><small>پایان مشاوره</small><strong><bdi>{{ verta($consultationEnd)->format('Y/m/d') }}</bdi> · <bdi>{{ $consultationEnd->format('H:i') }}</bdi></strong></p></div>
        </div>
    </section>
    @endif

    @if(!$appointment->trashed() && request()->user()->can('update', $appointment))
    <div class="modal fade" id="quick-time-edit-modal" tabindex="-1" aria-labelledby="quick-time-edit-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content oc-billing-modal" dir="rtl">
        <form method="POST" action="{{ route('admin.consultation.call-reports.appointment-time.update', $appointment) }}">@csrf @method('PUT')
            <input type="hidden" name="quick_time_edit" value="1">
            <div class="modal-header"><h2 class="modal-title" id="quick-time-edit-title"><i class="fa-solid fa-clock"></i> ویرایش سریع زمان نوبت</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div>
            <div class="modal-body oc-stack">
                <div class="oc-case-confirm-summary"><span>بیمار <strong>{{ $appointment->user?->fullName ?: '—' }}</strong></span><span>پزشک <strong>{{ $appointment->doctor?->fullName ?: '—' }}</strong></span></div>
                <div class="oc-field"><label class="oc-label" for="quick-time-date">تاریخ جدید</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" id="quick-time-date" name="date" type="text" data-jdp inputmode="numeric" autocomplete="off" value="{{ old('date', verta($appointment->date_visit)->format('Y/m/d')) }}" placeholder="مثلاً ۱۴۰۵/۰۶/۱۸" required></div>@error('date')<small class="oc-error">{{ $message }}</small>@enderror</div>
                <div class="oc-form-grid"><div class="oc-field"><label class="oc-label" for="quick-time-start">ساعت شروع</label><input class="oc-input oc-ltr" id="quick-time-start" name="start_time" type="time" value="{{ old('start_time', substr((string) $appointment->start_time, 0, 5)) }}" required>@error('start_time')<small class="oc-error">{{ $message }}</small>@enderror</div><div class="oc-field"><label class="oc-label" for="quick-time-end">ساعت پایان</label><input class="oc-input oc-ltr" id="quick-time-end" name="end_time" type="time" value="{{ old('end_time', substr((string) $appointment->end_time, 0, 5)) }}" required>@error('end_time')<small class="oc-error">{{ $message }}</small>@enderror</div></div>
                <div class="oc-notice oc-notice-warning"><i class="fa-solid fa-triangle-exclamation"></i><p>در صورت ویرایش از این قسمت، تداخل نوبت‌ها در نظر گرفته نمی‌شود.</p></div>
                <label class="oc-check"><input type="checkbox" name="send_sms" value="1" @checked(old('send_sms'))><span><strong>ارسال پیامک تغییر زمان</strong><small class="oc-cell-sub">پیامک با همان سناریو و مشخصات تعریف‌شده در تنظیمات سیستم ارسال می‌شود.</small></span></label>
            </div>
            <div class="modal-footer"><button type="button" class="oc-btn" data-bs-dismiss="modal">انصراف</button><button type="submit" class="oc-btn oc-btn-primary"><i class="fa-solid fa-check"></i>ثبت زمان جدید</button></div>
        </form>
    </div></div></div>
    @endif

    <details class="oc-panel oc-alternate-phones" id="alternate-phones-panel" @if($errors->has('alternate_phone')) open @endif>
        <summary class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-phone"></i>شماره‌های ثابت بیمار</h2><p class="oc-help">مانند شماره موبایل؛ قابل استفاده برای همه نوبت‌های این بیمار</p></div><span class="oc-count-badge" id="alternate-phone-count">{{ $appointment->alternatePhones->count() }}</span></summary>
        <div class="oc-panel-body oc-stack">
            @if(!$alternatePhonesAvailable)
            <div class="oc-notice oc-notice-warning"><i class="fa-solid fa-triangle-exclamation"></i><p>این بخش پس از اجرای migration دیتابیس فعال می‌شود. سایر امکانات جزئیات نوبت در دسترس است.</p></div>
            @else
            <div class="oc-notice"><i class="fa-solid fa-circle-info"></i><p>هر شماره را جداگانه ثبت کنید. شماره باید دقیقاً ۱۱ رقم، ثابت و همراه با صفر و پیش‌شماره استان باشد؛ مثل <bdi class="oc-ltr">02112345678</bdi>. این شماره به حساب بیمار متصل می‌شود و در تمام نوبت‌های فعلی و آینده او مانند موبایل شناسایی می‌شود.</p></div>
            <div class="oc-case-history oc-alternate-phone-list" id="alternate-phone-list" @if($appointment->alternatePhones->isEmpty()) hidden @endif>
                @foreach($appointment->alternatePhones as $alternatePhone)
                <div class="oc-alternate-phone-row" data-phone-id="{{ $alternatePhone->id }}">
                    <div><strong class="oc-ltr"><bdi>{{ $alternatePhone->phone }}</bdi></strong><small class="oc-cell-sub">ثبت‌کننده: {{ $alternatePhone->creator?->fullName ?: '—' }} · <bdi>{{ verta($alternatePhone->created_at)->format('Y/m/d H:i') }}</bdi></small></div>
                    @if($canEditCase)<button class="oc-btn oc-btn-small oc-btn-danger alternate-phone-delete" type="button" data-delete-url="{{ route('admin.consultation.alternate-phones.destroy', [$appointment, $alternatePhone]) }}"><i class="fa-solid fa-trash"></i>حذف</button>@endif
                </div>
                @endforeach
            </div>
            <div class="oc-empty" id="alternate-phone-empty" @if($appointment->alternatePhones->isNotEmpty()) hidden @endif><i class="fa-solid fa-phone-slash"></i><h3>شماره ثابتی برای این بیمار ثبت نشده است</h3></div>
            @if($canEditCase)
            <form method="POST" action="{{ route('admin.consultation.alternate-phones.store', $appointment) }}" class="oc-alternate-phone-form" id="alternate-phone-form">@csrf
                <label class="oc-label" for="alternate-phone">شماره ثابت جدید</label><div class="oc-alternate-phone-controls"><input class="oc-input oc-ltr" id="alternate-phone" name="alternate_phone" value="{{ old('alternate_phone') }}" inputmode="numeric" autocomplete="tel" maxlength="11" pattern="0[1-8][0-9]{9}" placeholder="02112345678" required><button class="oc-btn oc-btn-primary" id="alternate-phone-submit" type="submit"><i class="fa-solid fa-plus"></i><span>افزودن</span></button></div><small class="oc-error" id="alternate-phone-error" @if(!$errors->has('alternate_phone')) hidden @endif>{{ $errors->first('alternate_phone') }}</small><div class="oc-notice oc-notice-success" id="alternate-phone-success" role="status" hidden><i class="fa-solid fa-circle-check"></i><p></p></div>
            </form>
            @endif
            @endif
        </div>
    </details>

    <section class="oc-panel oc-case-panel">
        <div class="oc-case-hero {{ $consultationCase->isClosed() ? 'is-completed' : 'is-open' }}">
            <div class="oc-case-state"><i class="fa-solid {{ $consultationCase->isClosed() ? 'fa-circle-check' : 'fa-file-medical' }}"></i><div><small>وضعیت پرونده مشاوره</small><strong>{{ $consultationCase->state === 'PATIENT_NO_SHOW' ? 'عدم حضور بیمار — تسویه کامل بدون بازگشت وجه' : ($consultationCase->state === 'COMPLETED' ? 'مشاوره تمام شده' : 'پرونده باز و قابل ثبت گزارش') }}</strong>@if($consultationCase->completed_at)<span>آخرین اتمام: <bdi>{{ verta($consultationCase->completed_at)->format('Y/m/d H:i:s') }}</bdi> توسط {{ $consultationCase->completedBy?->fullName ?: '—' }}</span>@endif</div></div>
            <div class="oc-actions">@if($billing)<a class="oc-btn" href="#consultation-billing"><i class="fa-solid fa-calculator"></i>محاسبه هزینه و کیف پول</a>@endif
            @if($canEditCase)
                @if($consultationCase->state === 'OPEN')
                @if($canRequestCallback)
                <button type="button" class="oc-btn oc-btn-primary" data-bs-toggle="modal" data-bs-target="#callback-request-modal"><i class="fa-solid fa-phone-volume"></i>تماس اتوماتیک</button>
                @elseif($showCallbackAction)
                <span class="oc-callback-unavailable"><button type="button" class="oc-btn" disabled><i class="fa-solid fa-phone-volume"></i>تماس اتوماتیک</button><small>{{ $callbackAvailable ? $callbackAvailability['reason'] : 'زیرساخت ثبت تماس اتوماتیک هنوز فعال نشده است.' }}</small></span>
                @endif
                <button type="button" class="oc-btn oc-btn-danger" data-bs-toggle="modal" data-bs-target="#complete-consultation-modal" title="{{ $consultationCase->reports->isEmpty() ? 'برای اتمام باید حداقل یک گزارش ثبت شده باشد' : 'اتمام و مسدودکردن اتصال مجدد' }}"><i class="fa-solid fa-lock"></i>اتمام مشاوره</button>
                @if($appointment->kind === \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP)
                <button type="button" class="oc-btn oc-btn-danger" data-bs-toggle="modal" data-bs-target="#patient-no-show-modal">عدم حضور بیمار</button>
                @endif
                @elseif($consultationCase->state === 'COMPLETED')<button type="button" class="oc-btn" data-bs-toggle="modal" data-bs-target="#reopen-consultation-modal"><i class="fa-solid fa-lock-open"></i>بازکردن مجدد پرونده</button>@endif
            @endif
            </div>
        </div>

        @if($canRequestCallback)
        <div class="modal fade" id="callback-request-modal" tabindex="-1" aria-labelledby="callback-request-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content oc-billing-modal" dir="rtl">
            <form method="POST" action="{{ route('admin.consultation.callback.store', $appointment) }}" id="callback-request-form">@csrf
                <div class="modal-header"><h2 class="modal-title" id="callback-request-title"><i class="fa-solid fa-phone-volume"></i> ثبت و ارسال درخواست تماس</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div>
                <div class="modal-body">
                    <div class="oc-notice"><i class="fa-solid fa-circle-info"></i><p>پس از تأیید، درخواست به سرور VoIP ارسال می‌شود. سرور ابتدا با بیمار تماس می‌گیرد و پس از پاسخ، تماس را به داخلی مشاور متصل می‌کند.</p></div>
                    <div class="oc-case-confirm-summary">
                        <span>بیمار <strong>{{ $appointment->user?->fullName ?: '—' }}</strong></span>
                        <span>شماره بیمار <strong class="oc-ltr">{{ $appointment->user?->mobile ?: '—' }}</strong></span>
                        <span>مشاور <strong>{{ $appointment->doctor?->fullName ?: '—' }}</strong></span>
                        <span>داخلی مشاور <strong class="oc-ltr">{{ $appointment->doctor?->consultationPractitioner?->extension ?: '—' }}</strong></span>
                        <span>نوبت <strong>#{{ $appointment->tracking_code ?: $appointment->id }}</strong></span>
                        <span>زمان نوبت <strong><bdi>{{ $appointment->date_visit ? verta($appointment->date_visit)->format('Y/m/d H:i') : '—' }}</bdi></strong></span>
                    </div>
                    <div class="oc-notice oc-notice-warning"><i class="fa-solid fa-triangle-exclamation"></i><p>شناسه یکتای درخواست خودکار ساخته می‌شود. فقط پاسخ HTTP 202 به معنی ثبت موفق درخواست در سرور تماس است.</p></div>
                </div>
                <div class="modal-footer"><button type="button" class="oc-btn" data-bs-dismiss="modal">انصراف</button><button type="submit" class="oc-btn oc-btn-primary" id="callback-request-submit"><i class="fa-solid fa-phone-volume"></i>تأیید و تماس با بیمار</button></div>
            </form>
        </div></div></div>
        @endif

        @if($canEditCase && $consultationCase->state === 'OPEN' && $appointment->kind === \Modules\AppointmentUser\Enum\AppointmentUserKindEnum::VOIP)
        <div class="modal fade" id="patient-no-show-modal" tabindex="-1" aria-labelledby="patient-no-show-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content oc-billing-modal" dir="rtl">
            <form method="POST" action="{{ route('admin.consultation.case.patient-no-show', $appointment) }}">@csrf
                <div class="modal-header"><h2 class="modal-title" id="patient-no-show-title">ثبت عدم حضور بیمار</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div>
                <div class="modal-body">
                    <p>فقط پس از پایان کامل بازه نوبت و در صورت نبود تماس بیمار در بازه نوبت مجاز است. تماس‌های پیش از شروع نوبت محاسبه نمی‌شوند. شرایط هنگام تأیید دوباره بررسی می‌شوند.</p>
                    @if($billing)
                    @php $noShowShare = min((int)$billing->total_paid_amount, app(\Modules\OnlineConsultation\Services\AppointmentBillingService::class)->amountForMinutes((int)$billing->payout_hourly_rate_snapshot, (int)$billing->reserved_minutes)); @endphp
                    <p>مدت مشمول هزینه: {{ $billing->reserved_minutes }} دقیقه · پرداخت بیمار: {{ number_format($billing->total_paid_amount) }} تومان</p>
                    <p>سهم مشاور: {{ number_format($noShowShare) }} تومان · سهم مجموعه: {{ number_format($billing->total_paid_amount - $noShowShare) }} تومان · بازگشت کیف پول: صفر</p>
                    @endif
                    <label class="oc-final-confirm"><input type="checkbox" name="no_show_confirmed" value="1" required><span>عدم حضور بیمار و تسویه قطعی کل زمان رزروشده بدون بازگشت وجه را تأیید می‌کنم.</span></label>
                </div>
                <div class="modal-footer"><button type="button" class="oc-btn" data-bs-dismiss="modal">انصراف</button><button type="submit" class="oc-btn oc-btn-danger">تأیید عدم حضور و تسویه کامل</button></div>
            </form>
        </div></div></div>
        @endif
        @if($consultationCase->state === 'PATIENT_NO_SHOW')
        <div class="oc-notice oc-notice-warning"><p>علت بسته‌شدن: عدم حضور بیمار. کل زمان رزروشده مشمول هزینه است؛ وجهی به کیف پول بیمار بازگردانده نشده است. سابقه بررسی و مبالغ قطعی در حسابرسی همین صفحه ثبت شده‌اند.</p></div>
        @endif

        @if($canEditCase && $consultationCase->state === 'OPEN')
        <form method="POST" action="{{ route('admin.consultation.case.reports.store', $appointment) }}" class="oc-case-form" id="consultation-report-form">
            @csrf
            <div class="oc-case-form-title"><div><i class="fa-solid fa-pen-to-square"></i><strong>ثبت گزارش جدید مشاوره</strong><small>این گزارش حذف یا جایگزین نمی‌شود و با تاریخ دقیق در پرونده بیمار باقی می‌ماند.</small></div><span class="oc-badge">گزارش شماره {{ $consultationCase->reports->count() + 1 }}</span></div>
            <div class="oc-case-form-grid">
                <div class="oc-field"><label class="oc-label" for="outcome">نتیجه مشاوره</label><select class="oc-input" id="outcome" name="outcome" required><option value="">انتخاب نتیجه…</option>@foreach($outcomes as $value=>$label)<option value="{{ $value }}" @selected(old('outcome')===$value)>{{ $label }}</option>@endforeach</select>@error('outcome')<small class="oc-error">{{ $message }}</small>@enderror</div>
                <div class="oc-field"><label class="oc-label" for="report-subject">عنوان گزارش</label><input class="oc-input" id="report-subject" name="subject" minlength="3" maxlength="200" value="{{ old('subject') }}" placeholder="مثلاً بررسی روند درمان و توصیه‌های جلسه" required>@error('subject')<small class="oc-error">{{ $message }}</small>@enderror</div>
                <div class="oc-field"><label class="oc-label" for="follow-up-date">تاریخ و ساعت گزارش</label><div class="oc-follow-up-fields"><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" data-jdp id="follow-up-date" name="follow_up_date" type="text" inputmode="numeric" autocomplete="off" value="{{ old('follow_up_date', $reportDefaultDate) }}" placeholder="تاریخ شمسی" required></div><input class="oc-input oc-ltr" id="follow-up-time" name="follow_up_time" type="time" value="{{ old('follow_up_time', $reportDefaultTime) }}" aria-label="ساعت گزارش" required></div><small class="oc-help">تاریخ و ساعت فعلی به‌صورت پیش‌فرض درج شده و قبل از ثبت قابل تغییر است.</small>@error('follow_up_date')<small class="oc-error">{{ $message }}</small>@enderror @error('follow_up_time')<small class="oc-error">{{ $message }}</small>@enderror @error('follow_up_at')<small class="oc-error">{{ $message }}</small>@enderror</div>
                <div class="oc-field oc-case-report-field"><label class="oc-label" for="report-text">شرح دقیق گزارش</label><textarea class="oc-input" id="report-text" name="report_text" rows="7" minlength="10" maxlength="10000" placeholder="شرح وضعیت بیمار، نکات مطرح‌شده، توصیه‌ها، تصمیم نهایی و موارد لازم برای جلسه بعد را دقیق بنویسید…" required>{{ old('report_text') }}</textarea><div class="oc-report-counter"><small>حداقل ۱۰ و حداکثر ۱۰٬۰۰۰ نویسه</small><bdi><span id="report-char-count">0</span> / 10000</bdi></div>@error('report_text')<small class="oc-error">{{ $message }}</small>@enderror</div>
            </div>
            <div class="oc-case-form-actions"><span><i class="fa-solid fa-shield-halved"></i> نام ثبت‌کننده و زمان دقیق به‌صورت خودکار ذخیره می‌شود.</span><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i>ثبت گزارش در پرونده</button></div>
        </form>
        @elseif(!$canEditCase)<div class="oc-notice"><i class="fa-solid fa-eye"></i><p>شما دسترسی مشاهده پرونده را دارید؛ ثبت گزارش و تغییر وضعیت فقط برای مشاور همین نوبت یا مدیر کل فعال است.</p></div>
        @endif
    </section>

    <section class="oc-panel oc-note-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-note-sticky"></i>توضیحات این نوبت</h2><p class="oc-help">برای هر نوبت فقط یک توضیح ثبت می‌شود و مشخصات ثبت‌کننده نگهداری خواهد شد.</p></div>@if($consultationCase->appointment_note)<span class="oc-badge oc-badge-success"><i class="fa-solid fa-check"></i>ثبت شده</span>@endif</div>
        @if($consultationCase->appointment_note)
            <div class="oc-note-view"><div class="oc-note-author"><span class="oc-case-report-icon"><i class="fa-solid {{ $consultationCase->note_author_role === 'PRACTITIONER' ? 'fa-user-doctor' : 'fa-user-shield' }}"></i></span><div><strong>{{ $consultationCase->noteAuthor?->fullName ?: 'کاربر حذف‌شده' }}</strong><small>{{ $consultationCase->note_author_role === 'PRACTITIONER' ? 'پزشک / مشاور این نوبت' : 'مدیر' }} · <bdi>{{ $consultationCase->note_created_at ? verta($consultationCase->note_created_at)->format('Y/m/d H:i:s') : '—' }}</bdi></small></div></div><p>{{ $consultationCase->appointment_note }}</p></div>
        @elseif($canEditCase)
            <form method="POST" action="{{ route('admin.consultation.case.note.store', $appointment) }}" class="oc-note-form">@csrf<div class="oc-field"><label class="oc-label" for="appointment-note">متن توضیحات</label><textarea class="oc-input" id="appointment-note" name="appointment_note" rows="4" minlength="3" maxlength="5000" placeholder="توضیحی که لازم است برای همین نوبت در پرونده باقی بماند…" required>{{ old('appointment_note') }}</textarea>@error('appointment_note')<small class="oc-error">{{ $message }}</small>@enderror</div><div class="oc-case-form-actions"><span><i class="fa-solid fa-circle-info"></i>پس از ثبت، توضیح دوم برای این نوبت قابل افزودن نیست.</span><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i>ثبت توضیحات نوبت</button></div></form>
        @else
            <div class="oc-empty oc-note-empty"><i class="fa-solid fa-note-sticky"></i><h3>توضیحی برای این نوبت ثبت نشده است</h3></div>
        @endif
    </section>

    <section class="oc-panel"><div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-clock-rotate-left"></i>گزارش‌های همین نوبت</h2><p class="oc-help">برای مشاهده متن کامل روی هر گزارش کلیک کنید.</p></div><span class="oc-count-badge">{{ $consultationCase->reports->count() }}</span></div>
        <div class="oc-case-history">@forelse($consultationCase->reports as $report)<details class="oc-case-report"><summary><span class="oc-case-report-icon"><i class="fa-solid fa-notes-medical"></i></span><span class="oc-case-report-main"><strong>{{ $report->subject }}</strong><small>{{ $outcomes[$report->outcome] ?? $report->outcome }} · {{ $report->author?->fullName ?: 'کاربر حذف‌شده' }}</small></span><time><bdi>{{ verta($report->follow_up_at ?: $report->created_at)->format('Y/m/d H:i:s') }}</bdi></time><i class="fa-solid fa-chevron-down"></i></summary><div class="oc-case-report-body"><div class="oc-case-report-meta"><span>نتیجه: <strong>{{ $outcomes[$report->outcome] ?? $report->outcome }}</strong></span><span>ثبت‌کننده: <strong>{{ $report->author?->fullName ?: '—' }}</strong></span>@if($report->follow_up_at)<span>تاریخ و ساعت گزارش: <strong><bdi>{{ verta($report->follow_up_at)->format('Y/m/d H:i') }}</bdi></strong></span>@endif</div><p>{{ $report->report_text }}</p></div></details>@empty<div class="oc-empty"><i class="fa-solid fa-file-circle-plus"></i><h3>هنوز گزارشی برای این نوبت ثبت نشده است</h3><p>پس از ثبت اولین گزارش، امکان اتمام مشاوره فعال خواهد بود.</p></div>@endforelse</div>
    </section>

    <section class="oc-panel"><div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-folder-open"></i>سوابق قبلی این بیمار</h2><p class="oc-help">متن گزارش‌های نوبت‌های گذشته برای تصمیم‌گیری دقیق‌تر مشاور در دسترس است.</p></div><span class="oc-count-badge">{{ $previousConsultationReports->count() }}</span></div>
        <div class="oc-case-history">@forelse($previousConsultationReports as $report)<details class="oc-case-report is-previous"><summary><span class="oc-case-report-icon"><i class="fa-solid fa-file-waveform"></i></span><span class="oc-case-report-main"><strong>{{ $report->subject }}</strong><small>نوبت #{{ $report->appointment?->tracking_code ?: $report->appointment_id }} · {{ $report->appointment?->doctor?->fullName ?: '—' }}</small></span><time><bdi>{{ verta($report->follow_up_at ?: $report->created_at)->format('Y/m/d H:i:s') }}</bdi></time><i class="fa-solid fa-chevron-down"></i></summary><div class="oc-case-report-body"><div class="oc-case-report-meta"><span>نتیجه: <strong>{{ $outcomes[$report->outcome] ?? $report->outcome }}</strong></span><span>ثبت‌کننده: <strong>{{ $report->author?->fullName ?: '—' }}</strong></span>@if($report->follow_up_at)<span>تاریخ و ساعت گزارش: <strong><bdi>{{ verta($report->follow_up_at)->format('Y/m/d H:i') }}</bdi></strong></span>@endif</div><p>{{ $report->report_text }}</p></div></details>@empty<div class="oc-empty"><i class="fa-solid fa-folder-open"></i><h3>سابقه قبلی ثبت نشده است</h3><p>گزارش‌های نوبت‌های بعدی این بیمار نیز در همین بخش قابل مشاهده خواهند بود.</p></div>@endforelse</div>
    </section>

    @if($consultationCase->events->isNotEmpty())<section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-list-check"></i>تاریخچه تغییر وضعیت پرونده</h2></div><div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان دقیق</th><th>عملیات</th><th>ثبت‌کننده</th><th>دلیل</th></tr></thead><tbody>@foreach($consultationCase->events as $event)<tr><td><bdi>{{ verta($event->created_at)->format('Y/m/d H:i:s') }}</bdi></td><td><span class="oc-badge {{ $event->action === 'COMPLETED' ? 'oc-badge-success' : '' }}">{{ ['COMPLETED'=>'اتمام مشاوره', 'REOPENED'=>'بازگشایی پرونده', 'PATIENT_NO_SHOW'=>'عدم حضور بیمار و تسویه کامل', 'NO_SHOW_CALL_RECEIVED'=>'تماس دریافت‌شده پس از ثبت عدم حضور؛ نیازمند بررسی'][$event->action] ?? $event->action }}</span></td><td>{{ $event->actor?->fullName ?: data_get($event->snapshot, 'actor_name', '—') }}</td><td>{{ $event->reason ?: '—' }}@if($event->action === 'PATIENT_NO_SHOW')<small class="oc-cell-sub">تعداد تماس بیمار هنگام ثبت: {{ data_get($event->snapshot, 'patient_calls_count') }} · پایان بازه بررسی‌شده: <bdi>{{ verta(data_get($event->snapshot, 'appointment_end_at'))->format('Y/m/d H:i:s') }}</bdi></small><small class="oc-cell-sub">مبالغ هنگام ثبت (تومان): پرداخت {{ number_format(data_get($event->snapshot, 'billing.total_paid_amount', 0)) }} · سهم مشاور {{ number_format(data_get($event->snapshot, 'billing.practitioner_earned_amount', 0)) }} · سهم مجموعه {{ number_format(data_get($event->snapshot, 'billing.platform_profit_amount', 0)) }} · بازگشت ۰</small>@endif</td></tr>@endforeach</tbody></table></div></section>@endif
    <div class="oc-stats oc-appointment-stats">
        @foreach([
            ['تعداد تماس', $stats['calls'], null, 'fa-phone', ''], ['پاسخ داده‌شده', $stats['answered'], null, 'fa-check', 'oc-stat-green'],
            ['بی‌پاسخ در زمان نوبت', $stats['unanswered'], null, 'fa-phone-slash', 'oc-stat-orange'], ['تماس زودهنگام', $stats['early'], 'در آمار بی‌پاسخ محاسبه نمی‌شود', 'fa-clock', ''], ['کل مکالمه', $duration($stats['talk_seconds']), null, 'fa-comments', 'oc-stat-purple'],
            ['کل انتظار', $duration($stats['wait_seconds']), null, 'fa-hourglass-half', ''], ['میانگین مکالمه', $duration($stats['average_talk_seconds']), null, 'fa-chart-simple', ''],
            ['تماس کوتاه؛ قطع مشاور', $stats['consultant_hangups'], 'تلفن '.$stats['phone_hangups'].'  ·  سافت‌فون '.$stats['softphone_hangups'], 'fa-phone-slash', $stats['consultant_hangups'] ? 'oc-stat-danger' : ''],
            ['پایان عادی توسط مشاور', $stats['completed_consultant_hangups'], 'بیشتر از حد '.($shortCallThresholdSeconds / 60).' دقیقه', 'fa-circle-check', 'oc-stat-green'],
            ['عدم پاسخ در موعد', $stats['consultant_no_answers'], null, 'fa-triangle-exclamation', $stats['consultant_no_answers'] ? 'oc-stat-danger' : ''],
            ['درخواست تماس مشاور', $stats['callback_requests'], 'ثبت مستقل از گزارش نهایی تماس', 'fa-phone-volume', 'oc-stat-purple'],
        ] as [$label,$value,$detail,$icon,$tone])<div class="oc-panel oc-stat {{ $tone }}"><div class="oc-stat-icon"><i class="fa-solid {{ $icon }}"></i></div><div class="oc-stat-content"><span>{{ $label }}</span><strong>{{ $value }}</strong>@if($detail)<small class="oc-stat-detail">{{ $detail }}</small>@endif</div></div>@endforeach
    </div>
    @if($stats['consultant_hangups'] || $stats['consultant_no_answers'])<div class="oc-alert"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>هشدار مدیریتی:</strong> در این نوبت {{ $stats['consultant_hangups'] }} تماس کوتاه با قطع مشاور و {{ $stats['consultant_no_answers'] }} مورد عدم پاسخ‌گویی مشاور در موعد نوبت ثبت شده است. تماس‌های بیشتر از {{ $shortCallThresholdSeconds / 60 }} دقیقه در این هشدار نیستند.</p></div>@endif
    @if($hangupIncidents->isNotEmpty() || $noAnswerIncidents->isNotEmpty())
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title oc-text-danger"><i class="fa-solid fa-triangle-exclamation"></i>جزئیات هشدارهای مدیریتی این نوبت</h2></div><div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>نوع</th><th>زمان دقیق</th><th>شناسه تماس</th><th>داخلی</th><th>روش / مدت زنگ</th><th>کانال</th></tr></thead><tbody>
        @foreach($hangupIncidents as $incident)<tr class="oc-row-danger"><td><span class="oc-badge oc-badge-danger">تماس کوتاه؛ قطع مشاور</span></td><td><bdi>{{ verta($incident->hung_up_at)->format('Y/m/d H:i:s') }}</bdi></td><td class="oc-ltr">{{ $incident->call_id }}</td><td class="oc-ltr">{{ $incident->extension ?: '—' }}</td><td>{{ $incident->hangup_via === 'PHONE' ? 'تلفن مشاور' : 'سافت‌فون مشاور' }}</td><td class="oc-ltr">{{ $incident->channel ?: '—' }}</td></tr>@endforeach
        @foreach($noAnswerIncidents as $incident)<tr class="oc-row-danger"><td><span class="oc-badge oc-badge-danger">عدم پاسخ در موعد</span></td><td><bdi>{{ verta($incident->no_answer_at)->format('Y/m/d H:i:s') }}</bdi></td><td class="oc-ltr">{{ $incident->call_id }}</td><td class="oc-ltr">{{ $incident->extension ?: '—' }}</td><td>زنگ‌خوردن: <bdi class="oc-ltr">{{ $duration($incident->ring_duration_seconds) }}</bdi></td><td class="oc-ltr">{{ $incident->channel ?: '—' }}</td></tr>@endforeach
    </tbody></table></div></section>
    @endif
    @if($stats['last_call_at'])<div class="oc-notice"><i class="fa-solid fa-clock-rotate-left"></i><p>آخرین تماس این نوبت در <strong><bdi>{{ verta($stats['last_call_at'])->format('Y/m/d H:i:s') }}</bdi></strong> ثبت شده است.</p></div>@endif
    @if($callbackRequests->isNotEmpty())
    <section class="oc-panel"><div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-phone-volume"></i>تاریخچه درخواست تماس مشاور</h2><p class="oc-help">زمان کلیک مشاور و نتیجه ارسال به سرور مستقل از گزارش نهایی تماس نگهداری می‌شود.</p></div><span class="oc-count-badge">{{ $callbackRequests->count() }}</span></div><div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان دقیق درخواست</th><th>شناسه درخواست</th><th>بیمار / داخلی</th><th>درخواست‌کننده</th><th>نتیجه ارسال</th><th>پاسخ سرور</th><th>زمان پاسخ</th></tr></thead><tbody>
    @foreach($callbackRequests as $callback)
        @php
            $responseJson = $callback->response_payload;
            if (is_string($responseJson)) { $responseJson = json_decode($responseJson, true); }
            if (!is_array($responseJson) && is_string($callback->response_body)) {
                $decodedBody = json_decode($callback->response_body, true);
                $responseJson = is_array($decodedBody) ? $decodedBody : null;
            }
        @endphp
        <tr class="{{ $callback->status === 'FAILED' ? 'oc-row-danger' : '' }}"><td><bdi>{{ verta($callback->requested_at)->format('Y/m/d H:i:s') }}</bdi></td><td class="oc-ltr">{{ $callback->request_id }}@if($callback->callLog)<small class="oc-cell-sub"><a href="{{ route('admin.consultation.call-reports.call', $callback->callLog) }}">تماس {{ $callback->call_id }}</a></small>@endif</td><td><bdi class="oc-ltr">{{ $callback->patient_phone }}</bdi><small class="oc-cell-sub oc-ltr">داخلی {{ $callback->advisor_extension }}</small></td><td>{{ $callback->requester?->fullName ?: 'کاربر حذف‌شده' }}</td><td><span class="oc-badge {{ $callback->status === 'ACCEPTED' ? 'oc-badge-success' : ($callback->status === 'FAILED' ? 'oc-badge-danger' : 'oc-badge-warning') }}">{{ ['ACCEPTED'=>'پذیرفته شد','FAILED'=>'ناموفق','PENDING'=>'در حال ارسال'][$callback->status] ?? $callback->status }}</span><small class="oc-cell-sub">HTTP {{ $callback->http_status ?: '—' }}</small>@if($callback->final_call_received_at)<small class="oc-cell-sub oc-text-success">گزارش نهایی تماس دریافت شد</small>@endif</td><td>@if($responseJson)<button type="button" class="oc-btn oc-btn-small callback-response-btn" data-response-b64="{{ base64_encode(json_encode($responseJson, JSON_UNESCAPED_UNICODE)) }}" data-request-id="{{ $callback->request_id }}"><i class="fa-solid fa-code"></i> مشاهده پاسخ JSON</button>@else{{ $callback->error_message ?: (data_get($callback->response_payload, 'message') ?: ($callback->response_body ?: '—')) }}@endif</td><td class="oc-ltr">{{ $callback->duration_ms !== null ? $callback->duration_ms.' ms' : '—' }}</td></tr>
    @endforeach
    </tbody></table></div></section>
    @endif
    <div class="modal fade" id="callback-response-modal" tabindex="-1" aria-labelledby="callback-response-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content oc-billing-modal" dir="rtl"><div class="modal-header"><h2 class="modal-title" id="callback-response-title"><i class="fa-solid fa-code"></i> پاسخ سرور درخواست تماس</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div><div class="modal-body"><p class="oc-help" id="callback-response-request-id"></p><pre id="callback-response-content" dir="ltr" style="white-space:pre-wrap;word-break:break-word;max-height:60vh;overflow:auto;background:#101827;color:#dbeafe;border-radius:10px;padding:18px;text-align:left"></pre></div></div></div></div>
    @if($billing)
    @php
        $usedMinutes = min($billing->reserved_minutes, (int) ceil($billing->billable_talk_seconds / 60));
        $effectiveRefund = $billing->refund_status === 'completed'
            ? max(0, (int) $billing->refunded_amount + (int) $billing->adjustments->sum('amount_change'))
            : (int) $billing->suggested_refund_amount;
        $effectiveUnusedMinutes = (int) ($billing->adjustments->last()?->corrected_unused_minutes ?? $billing->approved_unused_minutes);
        $settledUsedMinutes = $billing->consultation_type === 'in_person'
            ? $usedMinutes
            : max(0, (int) $billing->reserved_minutes - $effectiveUnusedMinutes);
        $billingStatus = ['pending'=>'در انتظار تأیید','approved'=>'تأییدشده','completed'=>($consultationCase->state === 'PATIENT_NO_SHOW' ? 'تسویه کامل بابت عدم حضور بیمار' : 'تسویه نهایی‌شده')][$billing->refund_status] ?? $billing->refund_status;
    @endphp
    <section class="oc-panel oc-finance-panel" id="consultation-billing">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-wallet"></i>محاسبه مالی و بازگشت وجه</h2><p class="oc-help">بازگشت وجه متناسب با مبلغ واقعی همین نوبت و مدت رزروشده محاسبه می‌شود؛ نرخ عمومی بیمار در این محاسبه دخالتی ندارد.</p></div><span class="oc-badge {{ $billing->refund_status === 'completed' ? 'oc-badge-success' : 'oc-badge-warning' }}">{{ $billingStatus }}</span></div>
        <div class="oc-panel-body">
            <div class="oc-finance-grid">
                @foreach([
                    ['مدت رزروشده',$billing->reserved_minutes.' دقیقه'],
                    ['مجموع مکالمه پاسخ‌داده‌شده',$duration($billing->answered_talk_seconds)],
                    ['زمان سربار اتصال و مکالمه (دقیقه)',(int)($billing->connection_overhead_minutes_snapshot ?? 0).' دقیقه (مقدار ذخیره‌شده این نوبت)'],
                    ['زمان مالی مؤثر',$usedMinutes.' دقیقه ('.$duration($billing->billable_talk_seconds).')'],
                    ['زمان باقی‌مانده قابل بازگشت',$effectiveUnusedMinutes.' دقیقه'],
                    ['زمان نهایی مشمول سهم',$settledUsedMinutes.' دقیقه'],
                    ['هزینه ساعتی مشاور',number_format($billing->payout_hourly_rate_snapshot).' تومان'],
                    ['مبلغ واقعی همین نوبت',number_format($billing->total_paid_amount).' تومان'],
                    ['مبلغ نهایی مصرف‌شده',number_format($billing->used_amount).' تومان'],
                    [$billing->refund_status === 'completed' ? 'هزینه برگشتی قطعی' : 'هزینه برگشتی پیشنهادی',number_format($effectiveRefund).' تومان'],
                    ['خالص پس از بازگشت',number_format((int)$billing->total_paid_amount - $effectiveRefund).' تومان'],
                    ['سهم کارشناس',number_format($billing->practitioner_earned_amount).' تومان'],
                    ['سود مجموعه',number_format($billing->platform_profit_amount).' تومان'],
                ] as [$label,$value])<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach
            </div>
            <div class="oc-notice" style="margin-top:20px;margin-bottom:0"><i class="fa-solid fa-circle-info"></i><p>سهم کارشناس از <strong>زمان نهایی مشمول هزینه</strong> محاسبه می‌شود؛ یعنی مدت رزرو منهای دقایق برگشتیِ تأییدشده. تغییر دستی دقایق بازگشت، سهم کارشناس و سود مجموعه را نیز هم‌زمان اصلاح می‌کند.</p></div>
            @if((int) $billing->payout_hourly_rate_snapshot <= 0 && (int) $billing->total_paid_amount > 0)<div class="oc-notice oc-notice-warning" style="margin-top:20px;margin-bottom:0"><i class="fa-solid fa-triangle-exclamation"></i><p>نرخ پرداختی این کارشناس هنوز ثبت نشده است؛ سهم کارشناس و سود مجموعه تا ثبت نرخ قطعی نیست.</p></div>@endif

            @if($billing->consultation_type === 'in_person')
                <div class="oc-notice" style="margin-top:20px;margin-bottom:0"><i class="fa-solid fa-circle-info"></i><p>این نوبت حضوری است؛ مبلغ قابل پرداخت پس از جلسه براساس زمان استفاده‌شده محاسبه می‌شود و عملیات بازگشت کیف پول ندارد.</p></div>
            @elseif($billing->refund_status !== 'completed')
            <div class="oc-billing-actions">
                @if($billing->refund_status === 'approved')
                    <div class="oc-notice"><i class="fa-solid fa-lock"></i><p>این محاسبه قبلاً تأیید شده و دقایق آن قفل است. برای تکمیل واریز، تأیید نهایی را انجام دهید.</p></div>
                @endif
                <button class="oc-btn oc-btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#billing-confirmation-modal"><i class="fa-solid fa-calculator"></i>تأیید محاسبه و واریز</button>
            </div>
            @else
                <div class="oc-refund-complete"><i class="fa-solid fa-circle-check"></i><div><strong>{{ $consultationCase->state === 'PATIENT_NO_SHOW' ? 'عدم حضور بیمار: تسویه کامل ثبت شد و هیچ مبلغی به کیف پول بازنگشت.' : 'محاسبه نهایی قفل شد؛ مبلغ بازگشت به کیف پول: '.number_format($billing->refunded_amount).' تومان.' }}</strong><p>مبنای ثبت‌شده: مبلغ نوبت {{ number_format($billing->total_paid_amount) }} تومان · زمان سربار اتصال و مکالمه: {{ (int)($billing->connection_overhead_minutes_snapshot ?? 0) }} دقیقه · مدت رزرو: {{ $billing->reserved_minutes }} دقیقه.</p><p>ثبت‌کننده: {{ $billing->approver?->fullName ?: '—' }} (#{{ $billing->approved_by ?: '—' }}) · زمان: <bdi>{{ $billing->approved_at ? verta($billing->approved_at)->format('Y/m/d H:i:s') : '—' }}</bdi> · نوبت: #{{ $appointment->id }} / {{ $appointment->tracking_code ?: 'بدون کد پیگیری' }} · تراکنش کیف پول: {{ $billing->wallet_transaction_id ? '#'.$billing->wallet_transaction_id : 'بدون تراکنش (مبلغ صفر)' }}</p>@if($billing->walletTransaction)<p>موجودی قبل: {{ number_format($billing->walletTransaction->balance_before) }} تومان · موجودی بعد: {{ number_format($billing->walletTransaction->balance_after) }} تومان</p>@endif</div></div>
                @php($effectiveMinutes = $billing->adjustments->last()?->corrected_unused_minutes ?? $billing->approved_unused_minutes)
                @can('SUPER_ADMIN')
                @if($consultationCase->state !== 'PATIENT_NO_SHOW')
                <details class="oc-correction"><summary>ثبت عملیات اصلاحی جدید</summary><form method="POST" action="{{ route('admin.consultation.billing.correct', $billing) }}" class="oc-approval-form">@csrf<input type="hidden" name="request_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><div class="oc-field"><label class="oc-label" for="corrected_unused_minutes">دقایق نهایی اصلاح‌شده</label><input class="oc-input" id="corrected_unused_minutes" name="corrected_unused_minutes" type="number" min="0" max="{{ $billing->reserved_minutes }}" value="{{ $effectiveMinutes }}" required></div><div class="oc-field"><label class="oc-label" for="correction_reason">دلیل اصلاح</label><input class="oc-input" id="correction_reason" name="reason" maxlength="2000" required></div><button class="oc-btn" type="submit">ثبت اصلاح مستقل</button></form><p class="oc-help">این عملیات رکورد بازگشت قبلی را تغییر نمی‌دهد و یک تراکنش افزایشی یا کاهشی مستقل در کیف پول ثبت می‌کند.</p></details>
                @endif
                @endcan
            @endif
        </div>
    </section>

    @if($billing->consultation_type !== 'in_person' && $billing->refund_status !== 'completed')
    <div class="modal fade" id="billing-confirmation-modal" tabindex="-1" aria-labelledby="billing-confirmation-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content oc-billing-modal" dir="rtl">
            <form method="POST" action="{{ route('admin.consultation.billing.approve', $billing) }}" id="billing-final-form">
                @csrf @method('PUT')
                <div class="modal-header"><h2 class="modal-title" id="billing-confirmation-title"><i class="fa-solid fa-wallet"></i> تأیید نهایی محاسبه و واریز کیف پول</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div>
                <div class="modal-body">
                    <div class="oc-notice oc-notice-warning"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>این عملیات مالی نهایی است.</strong> پس از تأیید، مبلغ به کیف پول بیمار واریز می‌شود و امکان ویرایش برای کاربران عادی وجود ندارد.</p></div>
                    <div class="oc-finance-grid oc-confirmation-grid">
                        <div><span>بیمار</span><strong>{{ $appointment->user?->fullName ?: '—' }}</strong></div>
                        <div><span>نوبت / کد پیگیری</span><strong>#{{ $appointment->id }} · {{ $appointment->tracking_code ?: '—' }}</strong></div>
                        <div><span>مبلغ واقعی همین نوبت</span><strong>{{ number_format($billing->total_paid_amount) }} تومان</strong></div>
                        <div><span>هزینه ساعتی مشاور</span><strong>{{ number_format($billing->payout_hourly_rate_snapshot) }} تومان</strong></div>
                        <div><span>مجموع مکالمه پاسخ‌داده‌شده</span><strong>{{ $duration($billing->answered_talk_seconds) }}</strong></div>
                        <div><span>زمان سربار اتصال و مکالمه (دقیقه)</span><strong>{{ (int)($billing->connection_overhead_minutes_snapshot ?? 0) }} دقیقه</strong></div>
                        <div><span>زمان مالی مؤثر</span><strong>{{ $usedMinutes }} دقیقه</strong></div>
                        <div><span>زمان نهایی مشمول سهم</span><strong id="modal-settled-used-minutes">{{ $settledUsedMinutes }} دقیقه</strong></div>
                        <div><span>زمان باقی‌مانده سیستم</span><strong>{{ $billing->system_unused_minutes }} دقیقه</strong></div>
                        <div><span>مبلغ واریزی به بیمار</span><strong id="modal-refund-amount">{{ number_format($billing->suggested_refund_amount) }} تومان</strong></div>
                        <div><span>سهم کارشناس</span><strong id="modal-practitioner-amount">{{ number_format($billing->practitioner_earned_amount) }} تومان</strong></div>
                        <div><span>سود مجموعه</span><strong id="modal-platform-profit">{{ number_format($billing->platform_profit_amount) }} تومان</strong></div>
                    </div>
                    <div class="oc-approval-form">
                        <div class="oc-field"><label class="oc-label" for="modal-approved-minutes">زمان نهایی مورد تأیید برای بازگشت</label><div class="oc-input-group"><input class="oc-input" id="modal-approved-minutes" name="approved_unused_minutes" type="number" min="0" max="{{ $billing->reserved_minutes }}" value="{{ old('approved_unused_minutes', $billing->approved_unused_minutes) }}" @readonly($billing->refund_status === 'approved') required><span>دقیقه</span></div><small class="oc-help">پزشک می‌تواند این زمان را پیش از تأیید نهایی تغییر دهد؛ اختلاف و دلیل آن برای مدیر ثبت می‌شود.</small>@error('approved_unused_minutes')<small class="oc-error">{{ $message }}</small>@enderror</div>
                        <div class="oc-field"><label class="oc-label" for="modal-reason">دلیل اصلاح یا توضیحات</label><textarea class="oc-input" id="modal-reason" name="reason" maxlength="2000" rows="3" placeholder="اگر زمان پیشنهادی سیستم را تغییر می‌دهید، دلیل الزامی است">{{ old('reason', $billing->adjustment_reason) }}</textarea>@error('reason')<small class="oc-error">{{ $message }}</small>@enderror</div>
                    </div>
                    <div class="oc-adjustment-preview" id="billing-adjustment-preview" hidden></div>
                    <label class="oc-final-confirm" id="billing-final-consent-box"><input type="checkbox" id="billing-final-consent"><span>تأیید می‌کنم زمان <strong id="modal-confirmed-minutes">{{ $billing->approved_unused_minutes }}</strong> دقیقه مورد تأیید است و می‌دانم پس از واریز وجه، این محاسبه قابل ویرایش نیست.</span></label>
                    <div class="oc-consent-error" id="billing-consent-error" role="alert" hidden><i class="fa-solid fa-circle-exclamation"></i> برای ثبت نهایی و واریز وجه، ابتدا باید عبارت تأیید بالا را تیک بزنید.</div>
                </div>
                <div class="modal-footer"><button type="button" class="oc-btn" data-bs-dismiss="modal">انصراف</button><button class="oc-btn oc-btn-primary" id="billing-final-submit" type="submit"><i class="fa-solid fa-lock"></i>تأیید نهایی و واریز به کیف پول</button></div>
            </form>
        </div></div>
    </div>
    @endif

    @if($billing->audits->isNotEmpty())
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-clock-rotate-left"></i>سابقه حسابرسی مالی</h2></div><div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان</th><th>عملیات</th><th>کاربر</th><th>سربار ذخیره‌شده</th><th>زمان مالی مؤثر</th><th>زمان سیستمی</th><th>زمان تأییدشده</th><th>مبلغ</th><th>توضیحات</th></tr></thead><tbody>@foreach($billing->audits as $audit)<tr><td><bdi>{{ verta($audit->created_at)->format('Y/m/d H:i:s') }}</bdi></td><td>{{ ['patient_no_show_settled'=>'تسویه کامل بابت عدم حضور بیمار','snapshot_created'=>'ایجاد Snapshot مالی','usage_recalculated'=>'محاسبه مجدد مکالمه','minutes_approved'=>'تأیید زمان','wallet_refunded'=>'بازگشت به کیف پول','refund_confirmed'=>'تأیید نهایی و واریز کیف پول','practitioner_adjusted_refund'=>'تغییر زمان توسط پزشک و واریز','admin_adjusted_refund'=>'تغییر زمان توسط مدیر و واریز','refund_corrected'=>'اصلاح مستقل بازگشت'][$audit->action] ?? $audit->action }}</td><td>{{ $audit->actor?->fullName ?: 'سیستم' }}@if($audit->actor_id)<small class="oc-cell-sub">#{{ $audit->actor_id }}</small>@endif</td><td>{{ (int)data_get($audit->snapshot, 'connection_overhead_minutes_snapshot', 0) }} دقیقه</td><td>{{ (int)ceil((int)data_get($audit->snapshot, 'billable_talk_seconds', 0) / 60) }} دقیقه</td><td>{{ $audit->system_unused_minutes }} دقیقه</td><td>{{ $audit->approved_unused_minutes }} دقیقه</td><td>{{ number_format($audit->amount) }} تومان</td><td>{{ $audit->reason ?: '—' }}</td></tr>@endforeach</tbody></table></div></section>
    @endif
    @if($billing->adjustments->isNotEmpty())
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-scale-balanced"></i>اصلاحات پس از بازگشت وجه</h2></div><div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان</th><th>دقایق قبلی</th><th>دقایق اصلاح‌شده</th><th>تغییر مبلغ</th><th>ثبت‌کننده</th><th>دلیل</th><th>تراکنش کیف پول</th></tr></thead><tbody>@foreach($billing->adjustments as $adjustment)<tr><td><bdi>{{ verta($adjustment->created_at)->format('Y/m/d H:i:s') }}</bdi></td><td>{{ $adjustment->previous_unused_minutes }}</td><td>{{ $adjustment->corrected_unused_minutes }}</td><td class="{{ $adjustment->amount_change > 0 ? 'oc-text-success' : 'oc-text-danger' }}">{{ $adjustment->amount_change > 0 ? '+' : '' }}{{ number_format($adjustment->amount_change) }} تومان</td><td>{{ $adjustment->actor?->fullName ?: '—' }}</td><td>{{ $adjustment->reason }}</td><td>#{{ $adjustment->wallet_transaction_id }}</td></tr>@endforeach</tbody></table></div></section>
    @endif
    @endif
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-list-ul"></i>تمام تماس‌های این نوبت</h2></div>
    @if($calls->isEmpty())<div class="oc-empty"><i class="fa-solid fa-phone-slash"></i><h3>تماسی ثبت نشده است</h3></div>@else<div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان ورود تماس</th><th>نوع تماس</th><th>شماره تماس‌گیرنده</th><th>نتیجه</th><th>پاسخ‌گو</th><th>مقصد</th><th>رویداد مشاور</th><th>انتظار</th><th>زنگ‌خوردن</th><th>مکالمه</th><th>مدت کل</th><th></th></tr></thead><tbody>
    @foreach($calls as $call)
        @php($early = $call->isEarlyCall())
        @php($missedInWindow = $call->countsAsUnanswered())
        @php($activeHangup = ! $early && $call->isConsultantHangupWarning($shortCallThresholdSeconds))
        @php($completedHangup = ! $early && $call->isCompletedConsultantHangup($shortCallThresholdSeconds))
        @php($activeNoAnswer = $call->occurredDuringAppointment() && $call->consultantNoAnswer)
        @php($patientHangup = ! $early && $call->wasDisconnectedByPatient())
        @php($normalizedCaller = $normalizeCallerPhone($call->patient_phone))
        @php($callerKind = $normalizedCaller === $patientMobile ? 'mobile' : (in_array($normalizedCaller, $patientLandlines, true) ? 'landline' : 'other'))
        <tr class="{{ ($activeHangup || $activeNoAnswer) ? 'oc-row-danger' : '' }}"><td><bdi>{{ $call->call_entered_at ? verta($call->call_entered_at)->format('Y/m/d H:i:s') : '—' }}</bdi><small class="oc-cell-sub oc-ltr">شناسه فنی تماس: {{ $call->call_id }}</small></td><td>@if($call->direction === 'OUTBOUND')<span class="oc-badge oc-badge-success"><i class="fa-solid fa-arrow-up-right-from-square"></i> خروجی؛ درخواست مشاور</span>@elseif($call->direction === 'INBOUND')<span class="oc-badge"><i class="fa-solid fa-arrow-down"></i> ورودی؛ تماس بیمار</span>@else<span class="oc-badge">جهت نامشخص</span>@endif @if(data_get($call->additional_data, 'request_id'))<small class="oc-cell-sub oc-ltr">{{ data_get($call->additional_data, 'request_id') }}</small>@endif</td><td><strong class="oc-ltr"><bdi>{{ $call->patient_phone ?: '—' }}</bdi></strong><small class="oc-cell-sub">{{ $callerKind === 'mobile' ? 'موبایل اصلی بیمار' : ($callerKind === 'landline' ? 'شماره ثابت بیمار' : 'شماره اعلام‌شده توسط VoIP') }}</small>@if($callerKind === 'landline' && $normalizedCaller !== $call->patient_phone)<small class="oc-cell-sub oc-ltr">شماره کامل: {{ $normalizedCaller }}</small>@endif</td><td><span class="oc-badge oc-badge-{{ $callPresentation::tone($call, $shortCallThresholdSeconds) }}">{{ $callPresentation::label($call, $shortCallThresholdSeconds) }}</span>@if($call->surveyScore() !== null)<small class="oc-cell-sub oc-text-warning"><i class="fa-solid fa-star" aria-hidden="true"></i> امتیاز نظرسنجی: {{ $call->surveyScore() }} از ۵</small>@endif</td><td>{{ $call->operator?->fullName ?: ($call->responded_by ?: '—') }}</td><td class="oc-ltr">{{ $call->connected_destination ?: ($call->primary_extension ?: '—') }}</td><td>@if($early)<strong>خارج از بازه نوبت</strong><small class="oc-cell-sub">در آمار بی‌پاسخ محاسبه نشده</small>@elseif($completedHangup)<strong class="oc-text-success">مشاوره انجام شد</strong><small class="oc-cell-sub">پایان عادی توسط مشاور · بیشتر از حد {{ $shortCallThresholdSeconds / 60 }} دقیقه</small>@elseif($activeHangup)<strong class="oc-text-danger">تماس کوتاه؛ قطع توسط مشاور</strong><small class="oc-cell-sub">{{ $call->consultantHangup->hangup_via === 'PHONE' ? 'از تلفن' : 'از سافت‌فون' }} · <bdi>{{ verta($call->consultantHangup->hung_up_at)->format('Y/m/d H:i:s') }}</bdi></small>@elseif($activeNoAnswer)<strong class="oc-text-danger">پاسخ نداد</strong><small class="oc-cell-sub"><bdi>{{ verta($call->consultantNoAnswer->no_answer_at)->format('Y/m/d H:i:s') }}</bdi></small>@elseif($patientHangup)<strong class="oc-text-success">پایان تماس توسط بیمار</strong><small class="oc-cell-sub">بدون رویداد قطع مشاور</small>@elseif($missedInWindow)<strong>پاسخ داده نشد</strong><small class="oc-cell-sub">نتیجه خام VoIP: {{ $call->final_result }}</small>@else—@endif</td><td class="oc-ltr">{{ $duration($call->wait_duration_seconds) }}</td><td class="oc-ltr">{{ $duration($call->ring_duration_seconds) }}</td><td class="oc-ltr"><strong>{{ $duration($call->talk_duration_seconds) }}</strong></td><td class="oc-ltr">{{ $duration($call->total_duration_seconds) }}</td><td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.call', $call) }}">همه جزئیات</a></td></tr>
    @endforeach
    </tbody></table></div>{{ $calls->links('onlineconsultation::components.pagination') }} @endif</section>

    @if($canEditCase)
        @if($consultationCase->state === 'OPEN')
        <div class="modal fade" id="complete-consultation-modal" tabindex="-1" aria-labelledby="complete-consultation-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content oc-billing-modal" dir="rtl"><form method="POST" action="{{ route('admin.consultation.case.complete', $appointment) }}" id="complete-consultation-form">@csrf<div class="modal-header"><h2 class="modal-title" id="complete-consultation-title"><i class="fa-solid fa-lock"></i> تأیید اتمام مشاوره</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div><div class="modal-body">
            @if($consultationCase->reports->isEmpty())
            <div class="oc-notice oc-notice-warning"><i class="fa-solid fa-file-circle-plus"></i><p><strong>هنوز امکان اتمام مشاوره وجود ندارد.</strong><br>ابتدا حداقل یک گزارش برای این نوبت ثبت کنید؛ سپس دوباره «اتمام مشاوره» را انتخاب کنید.</p></div>
            @else
            <div class="oc-alert"><i class="fa-solid fa-triangle-exclamation"></i><p>پس از اتمام، API وضعیت «مشاوره تمام شده» برمی‌گرداند و تماس VoIP دیگر به مشاور وصل نمی‌شود.</p></div><div class="oc-case-confirm-summary"><span>نوبت <strong>#{{ $appointment->tracking_code ?: $appointment->id }}</strong></span><span>تعداد گزارش‌ها <strong>{{ $consultationCase->reports->count() }}</strong></span></div><label class="oc-final-confirm"><input type="checkbox" name="completion_confirmed" value="1" id="completion-confirmed" required><span>گزارش‌ها را بررسی کردم و اتمام قطعی این مشاوره و مسدودشدن اتصال مجدد را تأیید می‌کنم.</span></label>
            @endif
        </div><div class="modal-footer"><button type="button" class="oc-btn" data-bs-dismiss="modal">{{ $consultationCase->reports->isEmpty() ? 'متوجه شدم' : 'انصراف' }}</button>@if($consultationCase->reports->isNotEmpty())<button class="oc-btn oc-btn-danger" id="complete-consultation-submit" type="submit" disabled><i class="fa-solid fa-lock"></i>تأیید و اتمام مشاوره</button>@endif</div></form></div></div></div>
        @if($billing?->refund_status === 'completed')
        <div class="modal fade" id="settled-not-completed-modal" tabindex="-1" aria-labelledby="settled-not-completed-title" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content oc-billing-modal" dir="rtl">
                <div class="modal-header"><h2 class="modal-title" id="settled-not-completed-title"><i class="fa-solid fa-circle-exclamation oc-text-warning"></i> اتمام ویزیت فراموش شده است</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div>
                <div class="modal-body"><div class="oc-notice oc-notice-warning" style="margin:0"><i class="fa-solid fa-wallet"></i><p><strong>تسویه این نوبت انجام شده، اما هنوز «اتمام مشاوره» را ثبت نکرده‌اید.</strong><br>برای نهایی‌شدن پرونده و جلوگیری از اتصال مجدد تماس، حتماً اتمام ویزیت را انجام دهید.</p></div>@if($consultationCase->reports->isEmpty())<p class="oc-help" style="margin-top:16px">پیش از اتمام، باید حداقل یک گزارش مشاوره ثبت کنید.</p>@endif</div>
                <div class="modal-footer"><button type="button" class="oc-btn" data-bs-dismiss="modal">بعداً انجام می‌دهم</button><button type="button" class="oc-btn oc-btn-danger" id="settled-complete-action"><i class="fa-solid {{ $consultationCase->reports->isEmpty() ? 'fa-file-circle-plus' : 'fa-lock' }}"></i>{{ $consultationCase->reports->isEmpty() ? 'ثبت گزارش و اتمام' : 'همین حالا اتمام ویزیت' }}</button></div>
            </div></div>
        </div>
        @endif
        @elseif($consultationCase->state === 'COMPLETED')
        <div class="modal fade" id="reopen-consultation-modal" tabindex="-1" aria-labelledby="reopen-consultation-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content oc-billing-modal" dir="rtl"><form method="POST" action="{{ route('admin.consultation.case.reopen', $appointment) }}" id="reopen-consultation-form">@csrf<div class="modal-header"><h2 class="modal-title" id="reopen-consultation-title"><i class="fa-solid fa-lock-open"></i> بازکردن مجدد پرونده</h2><button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button></div><div class="modal-body"><div class="oc-notice oc-notice-warning"><i class="fa-solid fa-circle-info"></i><p>با بازگشایی، امکان ثبت گزارش جدید و اتصال VoIP در بازه معتبر نوبت دوباره فعال می‌شود. این عملیات در تاریخچه باقی می‌ماند.</p></div><div class="oc-field"><label class="oc-label" for="reopen-reason">دلیل بازگشایی</label><textarea class="oc-input" id="reopen-reason" name="reopen_reason" minlength="5" maxlength="2000" rows="4" required>{{ old('reopen_reason') }}</textarea></div><label class="oc-final-confirm"><input type="checkbox" name="reopen_confirmed" value="1" id="reopen-confirmed" required><span>بازگشایی این پرونده و فعال‌شدن مجدد آن را تأیید می‌کنم.</span></label></div><div class="modal-footer"><button type="button" class="oc-btn" data-bs-dismiss="modal">انصراف</button><button class="oc-btn oc-btn-primary" id="reopen-consultation-submit" type="submit" disabled><i class="fa-solid fa-lock-open"></i>تأیید بازگشایی</button></div></form></div></div></div>
        @endif
    @endif
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const consultationClock = document.getElementById('consultation-clock');
    if (consultationClock) {
        const startAt = Number(consultationClock.dataset.start);
        const endAt = Number(consultationClock.dataset.end);
        const serverNowAtLoad = Number(consultationClock.dataset.serverNow);
        const browserNowAtLoad = Date.now();
        const title = document.getElementById('consultation-clock-title');
        const badge = document.getElementById('consultation-clock-badge');
        const message = document.getElementById('consultation-clock-message');
        const description = document.getElementById('consultation-clock-description');
        const countdown = document.getElementById('consultation-countdown');
        const ended = document.getElementById('consultation-clock-ended');
        const progress = document.getElementById('consultation-clock-progress');
        const number = new Intl.NumberFormat('fa-IR', { useGrouping: false });
        const pad = value => number.format(value).padStart(2, '۰');
        const units = Object.fromEntries(Array.from(countdown.querySelectorAll('[data-unit]')).map(el => [el.dataset.unit, el]));
        const endTime = consultationClock.dataset.endLabel;
        let currentState = '';

        function splitDuration(milliseconds) {
            const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
            return {
                days: Math.floor(totalSeconds / 86400),
                hours: Math.floor((totalSeconds % 86400) / 3600),
                minutes: Math.floor((totalSeconds % 3600) / 60),
                seconds: totalSeconds % 60,
                totalMinutes: Math.max(0, Math.ceil(totalSeconds / 60)),
            };
        }
        function renderCounter(duration) {
            units.days.textContent = number.format(duration.days);
            units.hours.textContent = pad(duration.hours);
            units.minutes.textContent = pad(duration.minutes);
            units.seconds.textContent = pad(duration.seconds);
        }
        function readable(duration) {
            if (duration.days > 0) return `${number.format(duration.days)} روز و ${number.format(duration.hours)} ساعت و ${number.format(duration.minutes)} دقیقه`;
            if (duration.hours > 0) return `${number.format(duration.hours)} ساعت و ${number.format(duration.minutes)} دقیقه`;
            return `${number.format(duration.minutes)} دقیقه و ${number.format(duration.seconds)} ثانیه`;
        }
        function setState(state) {
            if (state === currentState) return;
            currentState = state;
            consultationClock.classList.toggle('is-live', state === 'live');
            consultationClock.classList.toggle('is-ended', state === 'ended');
            countdown.hidden = state === 'ended';
            ended.hidden = state !== 'ended';
        }
        function updateClock() {
            const nowAt = serverNowAtLoad + (Date.now() - browserNowAtLoad);
            if (nowAt < startAt) {
                setState('waiting');
                const remaining = splitDuration(startAt - nowAt);
                renderCounter(remaining);
                title.textContent = 'شمارش معکوس تا شروع مشاوره';
                badge.querySelector('span').textContent = 'در انتظار شروع';
                message.textContent = `${readable(remaining)} تا نوبت باقی مانده است`;
                description.textContent = 'در زمان تعیین‌شده، وضعیت این کارت به‌صورت خودکار به «در حال مشاوره» تغییر می‌کند.';
                progress.style.width = '0%';
            } else if (nowAt < endAt) {
                setState('live');
                const remaining = splitDuration(endAt - nowAt);
                renderCounter(remaining);
                title.textContent = 'مشاوره در حال برگزاری است';
                badge.querySelector('span').textContent = 'اکنون در زمان مشاوره';
                message.textContent = `اکنون تا ساعت ${endTime} در زمان مشاوره هستید`;
                description.textContent = `از زمان مشاوره شما ${readable(remaining)} باقی مانده است.`;
                progress.style.width = `${Math.min(100, Math.max(0, ((nowAt - startAt) / (endAt - startAt)) * 100))}%`;
            } else {
                setState('ended');
                title.textContent = 'زمان مشاوره به پایان رسیده است';
                badge.querySelector('span').textContent = 'پایان یافته';
                message.textContent = `بازه مشاوره در ساعت ${endTime} به پایان رسید`;
                description.textContent = 'ساعت پایان ثبت‌شده این نوبت در بخش مقابل نمایش داده شده است.';
                progress.style.width = '100%';
            }
        }
        updateClock();
        window.setInterval(updateClock, 1000);
    }
    const alternatePanel = document.getElementById('alternate-phones-panel');
    const alternateForm = document.getElementById('alternate-phone-form');
    const alternateInput = document.getElementById('alternate-phone');
    const alternateSubmit = document.getElementById('alternate-phone-submit');
    const alternateList = document.getElementById('alternate-phone-list');
    const alternateEmpty = document.getElementById('alternate-phone-empty');
    const alternateCount = document.getElementById('alternate-phone-count');
    const alternateError = document.getElementById('alternate-phone-error');
    const alternateSuccess = document.getElementById('alternate-phone-success');
    const csrfToken = alternateForm?.querySelector('input[name="_token"]')?.value;
    const setAlternateCount = () => {
        if (alternateCount && alternateList) alternateCount.textContent = alternateList.querySelectorAll('.oc-alternate-phone-row').length;
    };
    const buildAlternatePhoneRow = function (phone) {
        const row = document.createElement('div');
        row.className = 'oc-alternate-phone-row';
        row.dataset.phoneId = phone.id;
        const info = document.createElement('div');
        const number = document.createElement('strong');
        number.className = 'oc-ltr';
        number.textContent = phone.number;
        const meta = document.createElement('small');
        meta.className = 'oc-cell-sub';
        meta.textContent = 'ثبت‌کننده: ' + phone.creator + ' · ' + phone.created_at;
        info.append(number, meta);
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'oc-btn oc-btn-small oc-btn-danger alternate-phone-delete';
        remove.dataset.deleteUrl = phone.delete_url;
        remove.innerHTML = '<i class="fa-solid fa-trash"></i>حذف';
        row.append(info, remove);
        return row;
    };
    if (alternateForm) {
        alternateForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            alternatePanel.open = true;
            alternateError.hidden = true;
            alternateSuccess.hidden = true;
            alternateSubmit.disabled = true;
            alternateSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>در حال بررسی…</span>';
            try {
                const response = await fetch(alternateForm.action, {
                    method: 'POST', body: new FormData(alternateForm), credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await response.json();
                if (!response.ok) throw payload;
                alternateList.appendChild(buildAlternatePhoneRow(payload.phone));
                alternateList.hidden = false;
                alternateEmpty.hidden = true;
                alternateInput.value = '';
                alternateSuccess.querySelector('p').textContent = payload.message;
                alternateSuccess.hidden = false;
                setAlternateCount();
            } catch (payload) {
                alternateError.textContent = payload?.errors?.alternate_phone?.[0] || payload?.message || 'ثبت شماره انجام نشد. دوباره تلاش کنید.';
                alternateError.hidden = false;
                alternateInput.focus();
            } finally {
                alternateSubmit.disabled = false;
                alternateSubmit.innerHTML = '<i class="fa-solid fa-plus"></i><span>افزودن</span>';
            }
        });
    }
    alternateList?.addEventListener('click', async function (event) {
        const button = event.target.closest('.alternate-phone-delete');
        if (!button || !confirm('این شماره از حساب بیمار و تمام نوبت‌هایش حذف شود؟')) return;
        button.disabled = true;
        try {
            const response = await fetch(button.dataset.deleteUrl, {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
            });
            const payload = await response.json();
            if (!response.ok) throw payload;
            button.closest('.oc-alternate-phone-row').remove();
            setAlternateCount();
            if (!alternateList.querySelector('.oc-alternate-phone-row')) {
                alternateList.hidden = true;
                alternateEmpty.hidden = false;
            }
            alternateSuccess.querySelector('p').textContent = payload.message;
            alternateSuccess.hidden = false;
        } catch (payload) {
            button.disabled = false;
            alternateError.textContent = payload?.message || 'حذف شماره انجام نشد.';
            alternateError.hidden = false;
        }
    });
    const responseModal = document.getElementById('callback-response-modal');
    const responseContent = document.getElementById('callback-response-content');
    const responseRequestId = document.getElementById('callback-response-request-id');
    document.querySelectorAll('.callback-response-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            let payload = button.dataset.responseB64 ? atob(button.dataset.responseB64) : '';
            try { payload = JSON.parse(payload); } catch (e) { /* داده از قبل رشته‌ای است */ }
            responseRequestId.textContent = 'شناسه درخواست: ' + (button.dataset.requestId || '—');
            responseContent.textContent = typeof payload === 'string' ? payload : JSON.stringify(payload, null, 2);
            if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(responseModal).show();
        });
    });
    if (window.jalaliDatepicker) {
        const iranianHolidays = @json(holidays_array());
        jalaliDatepicker.startWatch({
            zIndex: 99999,
            dayRendering: function(dayOptions) {
                const formatted = `${dayOptions.year}/${String(dayOptions.month).padStart(2, '0')}/${String(dayOptions.day).padStart(2, '0')}`;
                return { isHollyDay: iranianHolidays.includes(formatted) };
            }
        });
    }
    @if(old('quick_time_edit'))
    if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(document.getElementById('quick-time-edit-modal')).show();
    @endif
    @if($canEditCase && $consultationCase->state === 'OPEN' && $billing?->refund_status === 'completed')
    const settlementReminderElement = document.getElementById('settled-not-completed-modal');
    const settlementReminder = window.bootstrap && settlementReminderElement
        ? bootstrap.Modal.getOrCreateInstance(settlementReminderElement)
        : null;
    if (settlementReminder) settlementReminder.show();
    document.getElementById('settled-complete-action')?.addEventListener('click', function () {
        @if($consultationCase->reports->isEmpty())
        settlementReminderElement.addEventListener('hidden.bs.modal', function () {
            document.getElementById('consultation-report-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('outcome')?.focus({ preventScroll: true });
        }, { once: true });
        settlementReminder?.hide();
        @else
        settlementReminderElement.addEventListener('hidden.bs.modal', function () {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('complete-consultation-modal')).show();
        }, { once: true });
        settlementReminder?.hide();
        @endif
    });
    @endif
    const reportText = document.getElementById('report-text');
    const reportCount = document.getElementById('report-char-count');
    if (reportText && reportCount) {
        const updateReportCount = () => reportCount.textContent = new Intl.NumberFormat('fa-IR').format(reportText.value.length);
        reportText.addEventListener('input', updateReportCount);
        updateReportCount();
    }
    [['completion-confirmed', 'complete-consultation-submit'], ['reopen-confirmed', 'reopen-consultation-submit']].forEach(function (ids) {
        const checkbox = document.getElementById(ids[0]);
        const button = document.getElementById(ids[1]);
        if (checkbox && button) checkbox.addEventListener('change', () => button.disabled = !checkbox.checked);
    });
    const callbackForm = document.getElementById('callback-request-form');
    const callbackSubmit = document.getElementById('callback-request-submit');
    if (callbackForm && callbackSubmit) {
        callbackForm.addEventListener('submit', function () {
            callbackSubmit.disabled = true;
            callbackSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> در حال ارسال درخواست…';
        });
    }
});
</script>
@if($billing && $billing->consultation_type !== 'in_person' && $billing->refund_status !== 'completed')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const minutes = document.getElementById('modal-approved-minutes');
    const refund = document.getElementById('modal-refund-amount');
    const receivable = document.getElementById('modal-practitioner-amount');
    const platformProfit = document.getElementById('modal-platform-profit');
    const confirmedMinutes = document.getElementById('modal-confirmed-minutes');
    const adjustmentPreview = document.getElementById('billing-adjustment-preview');
    const reason = document.getElementById('modal-reason');
    const consent = document.getElementById('billing-final-consent');
    const consentBox = document.getElementById('billing-final-consent-box');
    const consentError = document.getElementById('billing-consent-error');
    const submit = document.getElementById('billing-final-submit');
    const total = {{ (int) $billing->total_paid_amount }};
    const payoutHourlyRate = {{ (int) $billing->payout_hourly_rate_snapshot }};
    const reservedMinutes = {{ (int) $billing->reserved_minutes }};
    const systemMinutes = {{ (int) $billing->system_unused_minutes }};
    const format = new Intl.NumberFormat('fa-IR');
    function updateCalculation() {
        const value = Math.max(0, Math.min(reservedMinutes, Number(minutes.value) || 0));
        const refundAmount = reservedMinutes > 0 ? Math.min(total, Math.round(((total * value / reservedMinutes) / 1000)) * 1000) : 0;
        const netAfterRefund = Math.max(0, total - refundAmount);
        const settledUsedMinutes = Math.max(0, reservedMinutes - value);
        const practitionerEarned = Math.min(netAfterRefund, Math.round((payoutHourlyRate * settledUsedMinutes) / 60000) * 1000);
        refund.textContent = format.format(refundAmount) + ' تومان';
        receivable.textContent = format.format(practitionerEarned) + ' تومان';
        platformProfit.textContent = format.format(netAfterRefund - practitionerEarned) + ' تومان';
        document.getElementById('modal-settled-used-minutes').textContent = format.format(settledUsedMinutes) + ' دقیقه';
        confirmedMinutes.textContent = format.format(value);
        const difference = value - systemMinutes;
        reason.required = difference !== 0;
        adjustmentPreview.hidden = difference === 0;
        adjustmentPreview.innerHTML = difference === 0 ? '' : '<i class="fa-solid fa-user-doctor"></i><strong> تغییر پزشک:</strong> زمان بازگشت ' + format.format(Math.abs(difference)) + ' دقیقه ' + (difference > 0 ? 'افزایش' : 'کاهش') + ' یافته است. ثبت دلیل این تغییر الزامی است.';
    }
    minutes.addEventListener('input', updateCalculation);
    consent.addEventListener('change', function () {
        consentError.hidden = consent.checked;
        consentBox.classList.toggle('has-error', !consent.checked);
    });
    document.getElementById('billing-final-form').addEventListener('submit', function (event) {
        if (!consent.checked) {
            event.preventDefault();
            consentError.hidden = false;
            consentBox.classList.add('has-error');
            consent.focus();
            return;
        }
        submit.disabled = true;
        submit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> در حال ثبت و واریز...';
    });
    updateCalculation();
    @if($errors->hasAny(['approved_unused_minutes', 'reason', 'refund']))
    if (window.bootstrap) bootstrap.Modal.getOrCreateInstance(document.getElementById('billing-confirmation-modal')).show();
    @endif
});
</script>
@endif
@endpush
