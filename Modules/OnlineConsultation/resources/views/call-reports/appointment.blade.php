@extends('onlineconsultation::shell')
@section('consultation-title', 'تماس‌های نوبت #'.$appointment->id)
@section('consultation-description', 'سوابق کامل تماس بیمار برای این نوبت')
@section('consultation-header-actions')<a class="oc-btn" href="{{ route('admin.consultation.call-reports.index') }}"><i class="fa-solid fa-arrow-right"></i>بازگشت به گزارش‌ها</a>@endsection
@section('consultation-content')
@php
    $duration = fn ($seconds) => sprintf('%02d:%02d:%02d', intdiv((int) $seconds, 3600), intdiv(((int) $seconds) % 3600, 60), ((int) $seconds) % 60);
    $results = ['ANSWERED'=>'پاسخ داده‌شده','NOANSWER'=>'بی‌پاسخ','BUSY'=>'مشغول','CALLER_ABANDONED'=>'قطع توسط بیمار','FAILED'=>'ناموفق','CHANUNAVAIL'=>'داخلی در دسترس نیست','CONGESTION'=>'اختلال شبکه','NOT_DIALED'=>'شماره‌گیری نشده','MISSING_EXTENSION'=>'داخلی تعریف نشده'];
@endphp
<div class="oc-stack">
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-calendar-check"></i>مشخصات نوبت</h2></div><div class="oc-panel-body oc-detail-grid">
        <div><span>بیمار</span><strong>{{ $appointment->user?->fullName ?: '—' }}</strong><small class="oc-ltr">{{ $appointment->user?->mobile ?: '—' }}</small></div>
        <div><span>پزشک / کارشناس</span><strong>{{ $appointment->doctor?->fullName ?: '—' }}</strong></div>
        <div><span>زمان نوبت</span><strong>@if($appointment->date_visit)<bdi>{{ verta($appointment->date_visit)->format('Y/m/d H:i') }}</bdi>@else—@endif</strong></div>
        <div><span>کد پیگیری</span><strong>{{ $appointment->tracking_code ?: '—' }}</strong></div>
    </div></section>
    <div class="oc-stats oc-stats-seven">
        @foreach([
            ['تعداد تماس', $stats['calls'], 'fa-phone', ''], ['پاسخ داده‌شده', $stats['answered'], 'fa-check', 'oc-stat-green'],
            ['بی‌پاسخ', $stats['unanswered'], 'fa-phone-slash', 'oc-stat-orange'], ['کل مکالمه', $duration($stats['talk_seconds']), 'fa-comments', 'oc-stat-purple'],
            ['کل انتظار', $duration($stats['wait_seconds']), 'fa-hourglass-half', ''], ['میانگین مکالمه', $duration($stats['average_talk_seconds']), 'fa-chart-simple', ''],
        ] as [$label,$value,$icon,$tone])<div class="oc-panel oc-stat {{ $tone }}"><div class="oc-stat-icon"><i class="fa-solid {{ $icon }}"></i></div><div><span>{{ $label }}</span><strong>{{ $value }}</strong></div></div>@endforeach
    </div>
    @if($stats['last_call_at'])<div class="oc-notice"><i class="fa-solid fa-clock-rotate-left"></i><p>آخرین تماس این نوبت در <strong><bdi>{{ verta($stats['last_call_at'])->format('Y/m/d H:i:s') }}</bdi></strong> ثبت شده است.</p></div>@endif
    @if($billing)
    @php
        $usedMinutes = min($billing->reserved_minutes, (int) ceil($billing->answered_talk_seconds / 60));
        $billingStatus = ['pending'=>'در انتظار تأیید','approved'=>'تأییدشده','completed'=>'بازگشت انجام‌شده'][$billing->refund_status] ?? $billing->refund_status;
    @endphp
    <section class="oc-panel oc-finance-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-wallet"></i>محاسبه مالی و بازگشت وجه</h2><p class="oc-help">مبالغ براساس نرخ ثبت‌شده در زمان نوبت محاسبه می‌شوند.</p></div><span class="oc-badge {{ $billing->refund_status === 'completed' ? 'oc-badge-success' : 'oc-badge-warning' }}">{{ $billingStatus }}</span></div>
        <div class="oc-panel-body">
            <div class="oc-finance-grid">
                @foreach([
                    ['مدت رزروشده',$billing->reserved_minutes.' دقیقه'],
                    ['مکالمه واقعی',$usedMinutes.' دقیقه ('.$duration($billing->answered_talk_seconds).')'],
                    ['زمان باقی‌مانده سیستمی',$billing->system_unused_minutes.' دقیقه'],
                    ['زمان باقی‌مانده تأییدشده',$billing->approved_unused_minutes.' دقیقه'],
                    ['نرخ ساعتی',number_format($billing->hourly_rate_snapshot).' تومان'],
                    ['هزینه کل نوبت',number_format($billing->total_paid_amount).' تومان'],
                    ['هزینه زمان استفاده‌شده',number_format($billing->used_amount).' تومان'],
                    [$billing->refund_status === 'completed' ? 'مبلغ بازگشت‌داده‌شده' : 'مبلغ پیشنهادی بازگشت',number_format($billing->refund_status === 'completed' ? $billing->refunded_amount : $billing->suggested_refund_amount).' تومان'],
                ] as [$label,$value])<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach
            </div>

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
                <div class="oc-refund-complete"><i class="fa-solid fa-circle-check"></i><div><strong>محاسبه نهایی قفل شد و {{ number_format($billing->refunded_amount) }} تومان به کیف پول بیمار واریز شد.</strong><p>ثبت‌کننده: {{ $billing->approver?->fullName ?: '—' }} (#{{ $billing->approved_by ?: '—' }}) · زمان: <bdi>{{ $billing->approved_at ? verta($billing->approved_at)->format('Y/m/d H:i:s') : '—' }}</bdi> · نوبت: #{{ $appointment->id }} / {{ $appointment->tracking_code ?: 'بدون کد پیگیری' }} · تراکنش کیف پول: {{ $billing->wallet_transaction_id ? '#'.$billing->wallet_transaction_id : 'بدون تراکنش (مبلغ صفر)' }}</p>@if($billing->walletTransaction)<p>موجودی قبل: {{ number_format($billing->walletTransaction->balance_before) }} تومان · موجودی بعد: {{ number_format($billing->walletTransaction->balance_after) }} تومان</p>@endif</div></div>
                @php($effectiveMinutes = $billing->adjustments->last()?->corrected_unused_minutes ?? $billing->approved_unused_minutes)
                @can('SUPER_ADMIN')
                <details class="oc-correction"><summary>ثبت عملیات اصلاحی جدید</summary><form method="POST" action="{{ route('admin.consultation.billing.correct', $billing) }}" class="oc-approval-form">@csrf<input type="hidden" name="request_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><div class="oc-field"><label class="oc-label" for="corrected_unused_minutes">دقایق نهایی اصلاح‌شده</label><input class="oc-input" id="corrected_unused_minutes" name="corrected_unused_minutes" type="number" min="0" max="{{ $billing->reserved_minutes }}" value="{{ $effectiveMinutes }}" required></div><div class="oc-field"><label class="oc-label" for="correction_reason">دلیل اصلاح</label><input class="oc-input" id="correction_reason" name="reason" maxlength="2000" required></div><button class="oc-btn" type="submit">ثبت اصلاح مستقل</button></form><p class="oc-help">این عملیات رکورد بازگشت قبلی را تغییر نمی‌دهد و یک تراکنش افزایشی یا کاهشی مستقل در کیف پول ثبت می‌کند.</p></details>
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
                        <div><span>هزینه نوبت</span><strong>{{ number_format($billing->total_paid_amount) }} تومان</strong></div>
                        <div><span>نرخ ساعتی شما</span><strong>{{ number_format($billing->hourly_rate_snapshot) }} تومان</strong></div>
                        <div><span>زمان مکالمه واقعی</span><strong>{{ $usedMinutes }} دقیقه</strong></div>
                        <div><span>زمان باقی‌مانده سیستم</span><strong>{{ $billing->system_unused_minutes }} دقیقه</strong></div>
                        <div><span>مبلغ واریزی به بیمار</span><strong id="modal-refund-amount">{{ number_format($billing->suggested_refund_amount) }} تومان</strong></div>
                        <div><span>دریافتی شما</span><strong id="modal-practitioner-amount">{{ number_format(max(0, $billing->total_paid_amount - $billing->suggested_refund_amount)) }} تومان</strong></div>
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
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-clock-rotate-left"></i>سابقه حسابرسی مالی</h2></div><div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان</th><th>عملیات</th><th>کاربر</th><th>زمان سیستمی</th><th>زمان تأییدشده</th><th>مبلغ</th><th>توضیحات</th></tr></thead><tbody>@foreach($billing->audits as $audit)<tr><td><bdi>{{ verta($audit->created_at)->format('Y/m/d H:i:s') }}</bdi></td><td>{{ ['snapshot_created'=>'ایجاد Snapshot مالی','usage_recalculated'=>'محاسبه مجدد مکالمه','minutes_approved'=>'تأیید زمان','wallet_refunded'=>'بازگشت به کیف پول','refund_confirmed'=>'تأیید نهایی و واریز کیف پول','practitioner_adjusted_refund'=>'تغییر زمان توسط پزشک و واریز','admin_adjusted_refund'=>'تغییر زمان توسط مدیر و واریز','refund_corrected'=>'اصلاح مستقل بازگشت'][$audit->action] ?? $audit->action }}</td><td>{{ $audit->actor?->fullName ?: 'سیستم' }}@if($audit->actor_id)<small class="oc-cell-sub">#{{ $audit->actor_id }}</small>@endif</td><td>{{ $audit->system_unused_minutes }} دقیقه</td><td>{{ $audit->approved_unused_minutes }} دقیقه</td><td>{{ number_format($audit->amount) }} تومان</td><td>{{ $audit->reason ?: '—' }}</td></tr>@endforeach</tbody></table></div></section>
    @endif
    @if($billing->adjustments->isNotEmpty())
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-scale-balanced"></i>اصلاحات پس از بازگشت وجه</h2></div><div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان</th><th>دقایق قبلی</th><th>دقایق اصلاح‌شده</th><th>تغییر مبلغ</th><th>ثبت‌کننده</th><th>دلیل</th><th>تراکنش کیف پول</th></tr></thead><tbody>@foreach($billing->adjustments as $adjustment)<tr><td><bdi>{{ verta($adjustment->created_at)->format('Y/m/d H:i:s') }}</bdi></td><td>{{ $adjustment->previous_unused_minutes }}</td><td>{{ $adjustment->corrected_unused_minutes }}</td><td class="{{ $adjustment->amount_change > 0 ? 'oc-text-success' : 'oc-text-danger' }}">{{ $adjustment->amount_change > 0 ? '+' : '' }}{{ number_format($adjustment->amount_change) }} تومان</td><td>{{ $adjustment->actor?->fullName ?: '—' }}</td><td>{{ $adjustment->reason }}</td><td>#{{ $adjustment->wallet_transaction_id }}</td></tr>@endforeach</tbody></table></div></section>
    @endif
    @endif
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-list-ul"></i>تمام تماس‌های این نوبت</h2></div>
    @if($calls->isEmpty())<div class="oc-empty"><i class="fa-solid fa-phone-slash"></i><h3>تماسی ثبت نشده است</h3></div>@else<div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان ورود تماس</th><th>نتیجه</th><th>پاسخ‌گو</th><th>مقصد</th><th>انتظار</th><th>زنگ‌خوردن</th><th>مکالمه</th><th>مدت کل</th><th></th></tr></thead><tbody>
    @foreach($calls as $call)<tr><td><bdi>{{ $call->call_entered_at ? verta($call->call_entered_at)->format('Y/m/d H:i:s') : '—' }}</bdi><small class="oc-cell-sub oc-ltr">{{ $call->call_id }}</small></td><td><span class="oc-badge {{ $call->final_result === 'ANSWERED' ? 'oc-badge-success' : 'oc-badge-warning' }}">{{ $results[$call->final_result] ?? $call->final_result }}</span></td><td>{{ $call->operator?->fullName ?: ($call->responded_by ?: '—') }}</td><td class="oc-ltr">{{ $call->connected_destination ?: ($call->primary_extension ?: '—') }}</td><td class="oc-ltr">{{ $duration($call->wait_duration_seconds) }}</td><td class="oc-ltr">{{ $duration($call->ring_duration_seconds) }}</td><td class="oc-ltr"><strong>{{ $duration($call->talk_duration_seconds) }}</strong></td><td class="oc-ltr">{{ $duration($call->total_duration_seconds) }}</td><td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.call', $call) }}">همه جزئیات</a></td></tr>@endforeach
    </tbody></table></div>@if($calls->hasPages())<div class="oc-pagination">{{ $calls->links() }}</div>@endif @endif</section>
</div>
@endsection
@push('scripts')
@if($billing && $billing->consultation_type !== 'in_person' && $billing->refund_status !== 'completed')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const minutes = document.getElementById('modal-approved-minutes');
    const refund = document.getElementById('modal-refund-amount');
    const receivable = document.getElementById('modal-practitioner-amount');
    const confirmedMinutes = document.getElementById('modal-confirmed-minutes');
    const adjustmentPreview = document.getElementById('billing-adjustment-preview');
    const reason = document.getElementById('modal-reason');
    const consent = document.getElementById('billing-final-consent');
    const consentBox = document.getElementById('billing-final-consent-box');
    const consentError = document.getElementById('billing-consent-error');
    const submit = document.getElementById('billing-final-submit');
    const hourlyRate = {{ (int) $billing->hourly_rate_snapshot }};
    const total = {{ (int) $billing->total_paid_amount }};
    const systemMinutes = {{ (int) $billing->system_unused_minutes }};
    const format = new Intl.NumberFormat('fa-IR');
    function updateCalculation() {
        const value = Math.max(0, Math.min({{ (int) $billing->reserved_minutes }}, Number(minutes.value) || 0));
        const refundAmount = Math.round((hourlyRate * value) / 60000) * 1000;
        refund.textContent = format.format(refundAmount) + ' تومان';
        receivable.textContent = format.format(Math.max(0, total - refundAmount)) + ' تومان';
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
    bootstrap.Modal.getOrCreateInstance(document.getElementById('billing-confirmation-modal')).show();
    @endif
});
</script>
@endif
@endpush
