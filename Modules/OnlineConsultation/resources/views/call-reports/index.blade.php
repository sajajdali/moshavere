@extends('onlineconsultation::shell')
@section('consultation-title', 'گزارش تماس‌های مشاوره')
@section('consultation-description', 'بررسی تماس‌ها، پاسخ‌گویی و مدت مکالمه به تفکیک هر نوبت')
@section('consultation-content')
@php
    $duration = fn ($seconds) => sprintf('%02d:%02d:%02d', intdiv((int) $seconds, 3600), intdiv(((int) $seconds) % 3600, 60), ((int) $seconds) % 60);
    $callPresentation = \Modules\OnlineConsultation\Support\CallResultPresentation::class;
@endphp
<div class="oc-stack">
    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title">عدم حضور بیمار — تسویه کامل بدون بازگشت وجه</h2><span>{{ $noShowAppointments->total() }} نوبت</span></div>
        <div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>نوبت / بیمار</th><th>مشاور</th><th>ثبت‌کننده / زمان</th><th>پرداخت</th><th>سهم مشاور</th><th>سهم مجموعه</th><th>بازگشت کیف پول</th></tr></thead><tbody>
        @forelse($noShowAppointments as $noShow)
            <tr><td><a href="{{ route('admin.consultation.call-reports.appointment', $noShow) }}">#{{ $noShow->tracking_code ?: $noShow->id }} · {{ $noShow->user?->fullName }}</a></td><td>{{ $noShow->doctor?->fullName }}</td><td>{{ $noShow->consultationCase->completedBy?->fullName ?: '—' }}<small class="oc-cell-sub">{{ verta($noShow->consultationCase->completed_at)->format('Y/m/d H:i:s') }}</small></td><td>{{ number_format($noShow->billingRecord?->total_paid_amount ?? 0) }}</td><td>{{ number_format($noShow->billingRecord?->practitioner_earned_amount ?? 0) }}</td><td>{{ number_format($noShow->billingRecord?->platform_profit_amount ?? 0) }}</td><td>۰ تومان</td></tr>
        @empty<tr><td colspan="7">عدم حضور تأییدشده‌ای در این بازه ثبت نشده است.</td></tr>@endforelse
        </tbody></table></div>{{ $noShowAppointments->links() }}
    </section>

    <div class="oc-stats">
        @foreach([
            ['کل تماس‌ها', $stats['calls'], 'fa-phone', ''],
            ['نوبت‌های دارای تماس', $stats['appointments'], 'fa-calendar-check', 'oc-stat-purple'],
            ['تماس پاسخ‌داده‌شده', $stats['answered'], 'fa-phone-volume', 'oc-stat-green'],
            ['مجموع مکالمه', $duration($stats['talk_seconds']), 'fa-clock', 'oc-stat-orange'],
            ['میانگین مکالمه', $duration($stats['average_talk_seconds']), 'fa-chart-simple', ''],
            ['درخواست تماس مشاور', $stats['callback_requests'], 'fa-phone-volume', 'oc-stat-purple'],
        ] as [$label, $value, $icon, $tone])
            <div class="oc-panel oc-stat {{ $tone }}"><div class="oc-stat-icon"><i class="fa-solid {{ $icon }}"></i></div><div><span>{{ $label }}</span><strong>{{ $value }}</strong></div></div>
        @endforeach
    </div>

    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-filter"></i>فیلتر گزارش</h2></div>
        <form class="oc-panel-body oc-filter-grid" method="GET" autocomplete="off">
            <div class="oc-field"><label class="oc-label" for="search">شماره، شناسه تماس یا کد پیگیری</label><input class="oc-input" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="مثلاً 0912 یا ABC123"></div>
            <div class="oc-field"><label class="oc-label" for="doctor_id">پزشک / مشاور VoIP</label><select class="oc-input" id="doctor_id" name="doctor_id"><option value="">همه پزشکان فعال</option>@foreach($practitioners as $profile)<option value="{{ $profile->user_id }}" @selected((int)($filters['doctor_id']??0)===$profile->user_id)>{{ $profile->display_name }}@if($profile->extension) — داخلی {{ $profile->extension }}@endif</option>@endforeach</select></div>
            <div class="oc-field"><label class="oc-label" for="result">نتیجه خام تماس</label><select class="oc-input" id="result" name="result"><option value="">همه نتایج</option>@foreach(['ANSWERED'=>'پاسخ داده‌شده','NOANSWER'=>'بی‌پاسخ','BUSY'=>'مشغول','CALLER_ABANDONED'=>'پایان تماس قبل از اتصال','FAILED'=>'ناموفق','CHANUNAVAIL'=>'داخلی در دسترس نیست','CONGESTION'=>'اختلال شبکه','NOT_DIALED'=>'شماره‌گیری نشده','MISSING_EXTENSION'=>'داخلی تعریف نشده'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['result'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="oc-field"><label class="oc-label" for="from">از تاریخ</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" data-jdp id="from" type="text" name="from" value="{{ $filters['from'] ?? '' }}" placeholder="مثلاً ۱۴۰۵/۰۶/۱۶"></div></div>
            <div class="oc-field"><label class="oc-label" for="to">تا تاریخ</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" data-jdp id="to" type="text" name="to" value="{{ $filters['to'] ?? '' }}" placeholder="مثلاً ۱۴۰۵/۰۶/۱۶"></div></div>
            <div class="oc-actions oc-filter-actions"><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i>اعمال فیلتر</button><a class="oc-btn" href="{{ route('admin.consultation.call-reports.index') }}">پاک‌کردن</a></div>
        </form>
    </section>

    @if($callbackRequests->isNotEmpty())
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-phone-volume"></i>آخرین درخواست‌های تماس مشاور</h2><p class="oc-help">این رکورد لحظه درخواست را نشان می‌دهد؛ نتیجه خود تماس پس از گزارش سرور در جدول تماس‌ها ثبت می‌شود.</p></div><span class="oc-count-badge">{{ $callbackRequests->count() }}</span></div>
        <div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان</th><th>شناسه درخواست</th><th>نوبت</th><th>بیمار</th><th>مشاور / داخلی</th><th>نتیجه ارسال</th><th>پاسخ سرور</th><th></th></tr></thead><tbody>
        @foreach($callbackRequests as $callback)<tr class="{{ $callback->status === 'FAILED' ? 'oc-row-danger' : '' }}"><td><bdi>{{ verta($callback->requested_at)->format('Y/m/d H:i:s') }}</bdi></td><td class="oc-ltr">{{ $callback->request_id }}</td><td>#{{ $callback->appointment_id }}<small class="oc-cell-sub">{{ $callback->appointment?->tracking_code ?: '—' }}</small></td><td>{{ $callback->appointment?->user?->fullName ?: '—' }}<small class="oc-cell-sub oc-ltr">{{ $callback->patient_phone }}</small></td><td>{{ $callback->appointment?->doctor?->fullName ?: '—' }}<small class="oc-cell-sub oc-ltr">داخلی {{ $callback->advisor_extension }}</small></td><td><span class="oc-badge {{ $callback->status === 'ACCEPTED' ? 'oc-badge-success' : ($callback->status === 'FAILED' ? 'oc-badge-danger' : 'oc-badge-warning') }}">{{ ['ACCEPTED'=>'پذیرفته شد','FAILED'=>'ناموفق','PENDING'=>'در حال ارسال'][$callback->status] ?? $callback->status }}</span><small class="oc-cell-sub">HTTP {{ $callback->http_status ?: '—' }} · {{ $callback->duration_ms !== null ? $callback->duration_ms.' ms' : '—' }}</small></td><td>{{ $callback->error_message ?: (data_get($callback->response_payload, 'message') ?: '—') }}</td><td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.appointment', $callback->appointment_id) }}">جزئیات</a></td></tr>@endforeach
        </tbody></table></div>
    </section>
    @endif

    @if($managementIncidents->isNotEmpty())
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title oc-text-danger"><i class="fa-solid fa-triangle-exclamation"></i>هشدارهای مدیریتی اخیر مشاوران</h2><p class="oc-help">قطع مشاور فقط برای مکالمه‌های حداکثر {{ $shortCallThresholdSeconds / 60 }} دقیقه هشدار است؛ تماس‌های طولانی‌تر، پایان عادی مشاوره محسوب می‌شوند.</p></div><span class="oc-badge oc-badge-danger">{{ $managementIncidents->count() }} رویداد</span></div>
        <div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان رویداد</th><th>نوع هشدار</th><th>نوبت</th><th>بیمار</th><th>مشاور</th><th>اطلاعات تماس</th><th></th></tr></thead><tbody>
        @foreach($managementIncidents as $incident)<tr class="oc-row-danger"><td><bdi>{{ verta($incident->incident_at)->format('Y/m/d H:i:s') }}</bdi></td><td><span class="oc-badge oc-badge-danger">{{ $incident->incident_type === 'HANGUP' ? 'تماس کوتاه؛ قطع مشاور' : 'عدم پاسخ در موعد' }}</span></td><td>#{{ $incident->appointment_id }}<small class="oc-cell-sub">{{ $incident->appointment?->tracking_code ?: '—' }}</small></td><td>{{ $incident->appointment?->user?->fullName ?: '—' }}<small class="oc-cell-sub oc-ltr">{{ $incident->appointment?->user?->mobile ?: '—' }}</small></td><td>{{ $incident->appointment?->doctor?->fullName ?: '—' }}</td><td class="oc-ltr">{{ $incident->extension ?: '—' }}<small class="oc-cell-sub">{{ $incident->incident_type === 'HANGUP' ? ($incident->hangup_via === 'PHONE' ? 'تلفن' : 'سافت‌فون') : 'زنگ: '.$duration($incident->ring_duration_seconds) }}</small><small class="oc-cell-sub">{{ $incident->call_id }}</small></td><td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.appointment', $incident->appointment_id) }}">بررسی نوبت</a></td></tr>@endforeach
        </tbody></table></div>
    </section>
    @endif

    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-calendar-days"></i>گزارش به تفکیک نوبت</h2><p class="oc-help">برای مشاهده تمام تماس‌ها و جزئیات یک نوبت، روی ردیف آن کلیک کنید.</p></div></div>
        @if($appointments->isEmpty())
            <div class="oc-empty"><i class="fa-solid fa-phone-slash"></i><h3>تماس مرتبط با نوبتی پیدا نشد</h3><p>پس از دریافت گزارش نهایی از سرور VoIP، اطلاعات اینجا نمایش داده می‌شود.</p></div>
        @else
            <div class="oc-table-wrap"><table class="oc-table oc-clickable-table"><thead><tr><th>نوبت</th><th>بیمار</th><th>پزشک / کارشناس</th><th>زمان نوبت</th><th>تعداد تماس</th><th>پاسخ / بی‌پاسخ</th><th>هشدار مشاور</th><th>مدت مکالمه</th><th>آخرین تماس</th><th></th></tr></thead><tbody>
            @foreach($appointments as $row)
                @php($item = $row->appointment)
                <tr class="{{ ($row->consultant_hangups || $row->consultant_no_answers) ? 'oc-row-danger' : '' }}" data-href="{{ route('admin.consultation.call-reports.appointment', $row->appointment_id) }}">
                    <td><strong>#{{ $row->appointment_id }}</strong>@if($item?->tracking_code)<small class="oc-cell-sub">{{ $item->tracking_code }}</small>@endif</td>
                    <td><span class="oc-person-name">{{ $item?->user?->fullName ?: '—' }}</span><small class="oc-cell-sub oc-ltr">{{ $item?->user?->mobile ?: '—' }}</small></td>
                    <td>{{ $item?->doctor?->fullName ?: '—' }}</td>
                    <td>@if($item?->date_visit)<bdi>{{ verta($item->date_visit)->format('Y/m/d H:i') }}</bdi>@else—@endif</td>
                    <td><span class="oc-count-badge">{{ $row->calls_count }}</span></td>
                    <td><span class="oc-text-success">{{ $row->answered_count }} پاسخ</span><small class="oc-cell-sub">{{ $row->unanswered_count }} بی‌پاسخ در زمان نوبت</small>@if($row->early_count)<small class="oc-cell-sub">{{ $row->early_count }} تماس زودهنگام (خارج از بی‌پاسخ)</small>@endif</td>
                    <td>@if($row->consultant_hangups || $row->consultant_no_answers)<span class="oc-badge oc-badge-danger">هشدار مدیریتی</span>@if($row->consultant_hangups)<small class="oc-cell-sub oc-text-danger">تماس کوتاه با قطع مشاور: {{ $row->consultant_hangups->total_count }} (تلفن {{ $row->consultant_hangups->phone_count }} / سافت‌فون {{ $row->consultant_hangups->softphone_count }})</small>@endif @if($row->consultant_no_answers)<small class="oc-cell-sub oc-text-danger">عدم پاسخ در موعد: {{ $row->consultant_no_answers->total_count }}</small>@endif @else—@endif</td>
                    <td class="oc-ltr">{{ $duration($row->talk_seconds) }}</td>
                    <td><bdi>{{ $row->last_call_at ? verta($row->last_call_at)->format('Y/m/d H:i:s') : '—' }}</bdi></td>
                    <td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.appointment', $row->appointment_id) }}">مشاهده<i class="fa-solid fa-arrow-left"></i></a></td>
                </tr>
            @endforeach
            </tbody></table></div>
            {{ $appointments->links('onlineconsultation::components.pagination') }}
        @endif
    </section>

    @if($unlinkedCalls->isNotEmpty())
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-link-slash"></i>تماس‌های بدون نوبت مرتبط</h2><p class="oc-help">این تماس‌ها نوبت معتبر نداشته‌اند یا هنگام تماس، سرویس بررسی نوبت در دسترس نبوده است.</p></div></div>
        <div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان</th><th>شماره بیمار</th><th>وضعیت</th><th>نتیجه</th><th>مدت کل</th><th></th></tr></thead><tbody>@foreach($unlinkedCalls as $call)<tr><td><bdi>{{ $call->call_entered_at ? verta($call->call_entered_at)->format('Y/m/d H:i:s') : '—' }}</bdi></td><td class="oc-ltr">{{ $call->patient_phone }}</td><td>{{ $call->appointment_state }}</td><td><span class="oc-badge oc-badge-{{ $callPresentation::rawTone($call->final_result) }}">{{ $callPresentation::rawLabel($call->final_result) }}</span></td><td class="oc-ltr">{{ $duration($call->total_duration_seconds) }}</td><td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.call', $call) }}">جزئیات</a></td></tr>@endforeach</tbody></table></div>
    </section>
    @endif
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
    document.querySelectorAll('.oc-clickable-table tr[data-href]').forEach(function(row){
        row.addEventListener('click', function(event){ if(!event.target.closest('a,button')) window.location.href=row.dataset.href; });
    });
});
</script>
@endpush
