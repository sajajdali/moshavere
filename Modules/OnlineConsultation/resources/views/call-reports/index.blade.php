@extends('onlineconsultation::shell')
@section('consultation-title', 'گزارش تماس‌های مشاوره')
@section('consultation-description', 'بررسی تماس‌ها، پاسخ‌گویی و مدت مکالمه به تفکیک هر نوبت')
@section('consultation-content')
@php
    $duration = fn ($seconds) => sprintf('%02d:%02d:%02d', intdiv((int) $seconds, 3600), intdiv(((int) $seconds) % 3600, 60), ((int) $seconds) % 60);
@endphp
<div class="oc-stack">
    <div class="oc-stats oc-stats-five">
        @foreach([
            ['کل تماس‌ها', $stats['calls'], 'fa-phone', ''],
            ['نوبت‌های دارای تماس', $stats['appointments'], 'fa-calendar-check', 'oc-stat-purple'],
            ['تماس پاسخ‌داده‌شده', $stats['answered'], 'fa-phone-volume', 'oc-stat-green'],
            ['مجموع مکالمه', $duration($stats['talk_seconds']), 'fa-clock', 'oc-stat-orange'],
            ['میانگین مکالمه', $duration($stats['average_talk_seconds']), 'fa-chart-simple', ''],
        ] as [$label, $value, $icon, $tone])
            <div class="oc-panel oc-stat {{ $tone }}"><div class="oc-stat-icon"><i class="fa-solid {{ $icon }}"></i></div><div><span>{{ $label }}</span><strong>{{ $value }}</strong></div></div>
        @endforeach
    </div>

    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-filter"></i>فیلتر گزارش</h2></div>
        <form class="oc-panel-body oc-filter-grid" method="GET" autocomplete="off">
            <div class="oc-field"><label class="oc-label" for="search">شماره، شناسه تماس یا کد پیگیری</label><input class="oc-input" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="مثلاً 0912 یا ABC123"></div>
            <div class="oc-field"><label class="oc-label" for="doctor_id">پزشک / مشاور VoIP</label><select class="oc-input" id="doctor_id" name="doctor_id"><option value="">همه پزشکان فعال</option>@foreach($practitioners as $profile)<option value="{{ $profile->user_id }}" @selected((int)($filters['doctor_id']??0)===$profile->user_id)>{{ $profile->display_name }}@if($profile->extension) — داخلی {{ $profile->extension }}@endif</option>@endforeach</select></div>
            <div class="oc-field"><label class="oc-label" for="result">نتیجه تماس</label><select class="oc-input" id="result" name="result"><option value="">همه نتایج</option>@foreach(['ANSWERED'=>'پاسخ داده‌شده','NOANSWER'=>'بی‌پاسخ','BUSY'=>'مشغول','CALLER_ABANDONED'=>'قطع توسط بیمار','FAILED'=>'ناموفق','CHANUNAVAIL'=>'داخلی در دسترس نیست','CONGESTION'=>'اختلال شبکه','NOT_DIALED'=>'شماره‌گیری نشده','MISSING_EXTENSION'=>'داخلی تعریف نشده'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['result'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="oc-field"><label class="oc-label" for="from">از تاریخ</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" data-jdp id="from" type="text" name="from" value="{{ $filters['from'] ?? '' }}" placeholder="مثلاً ۱۴۰۵/۰۶/۱۶"></div></div>
            <div class="oc-field"><label class="oc-label" for="to">تا تاریخ</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" data-jdp id="to" type="text" name="to" value="{{ $filters['to'] ?? '' }}" placeholder="مثلاً ۱۴۰۵/۰۶/۱۶"></div></div>
            <div class="oc-actions oc-filter-actions"><button class="oc-btn oc-btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i>اعمال فیلتر</button><a class="oc-btn" href="{{ route('admin.consultation.call-reports.index') }}">پاک‌کردن</a></div>
        </form>
    </section>

    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-calendar-days"></i>گزارش به تفکیک نوبت</h2><p class="oc-help">برای مشاهده تمام تماس‌ها و جزئیات یک نوبت، روی ردیف آن کلیک کنید.</p></div></div>
        @if($appointments->isEmpty())
            <div class="oc-empty"><i class="fa-solid fa-phone-slash"></i><h3>تماس مرتبط با نوبتی پیدا نشد</h3><p>پس از دریافت گزارش نهایی از سرور VoIP، اطلاعات اینجا نمایش داده می‌شود.</p></div>
        @else
            <div class="oc-table-wrap"><table class="oc-table oc-clickable-table"><thead><tr><th>نوبت</th><th>بیمار</th><th>پزشک / کارشناس</th><th>زمان نوبت</th><th>تعداد تماس</th><th>پاسخ / بی‌پاسخ</th><th>مدت مکالمه</th><th>آخرین تماس</th><th></th></tr></thead><tbody>
            @foreach($appointments as $row)
                @php($item = $row->appointment)
                <tr data-href="{{ route('admin.consultation.call-reports.appointment', $row->appointment_id) }}">
                    <td><strong>#{{ $row->appointment_id }}</strong>@if($item?->tracking_code)<small class="oc-cell-sub">{{ $item->tracking_code }}</small>@endif</td>
                    <td><span class="oc-person-name">{{ $item?->user?->fullName ?: '—' }}</span><small class="oc-cell-sub oc-ltr">{{ $item?->user?->mobile ?: '—' }}</small></td>
                    <td>{{ $item?->doctor?->fullName ?: '—' }}</td>
                    <td>@if($item?->date_visit)<bdi>{{ verta($item->date_visit)->format('Y/m/d H:i') }}</bdi>@else—@endif</td>
                    <td><span class="oc-count-badge">{{ $row->calls_count }}</span></td>
                    <td><span class="oc-text-success">{{ $row->answered_count }} پاسخ</span><small class="oc-cell-sub">{{ $row->unanswered_count }} بی‌پاسخ</small></td>
                    <td class="oc-ltr">{{ $duration($row->talk_seconds) }}</td>
                    <td><bdi>{{ $row->last_call_at ? verta($row->last_call_at)->format('Y/m/d H:i:s') : '—' }}</bdi></td>
                    <td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.appointment', $row->appointment_id) }}">مشاهده<i class="fa-solid fa-arrow-left"></i></a></td>
                </tr>
            @endforeach
            </tbody></table></div>
            @if($appointments->hasPages())<div class="oc-pagination">{{ $appointments->links() }}</div>@endif
        @endif
    </section>

    @if($unlinkedCalls->isNotEmpty())
    <section class="oc-panel">
        <div class="oc-panel-header"><div><h2 class="oc-panel-title"><i class="fa-solid fa-link-slash"></i>تماس‌های بدون نوبت مرتبط</h2><p class="oc-help">این تماس‌ها نوبت معتبر نداشته‌اند یا هنگام تماس، سرویس بررسی نوبت در دسترس نبوده است.</p></div></div>
        <div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>زمان</th><th>شماره بیمار</th><th>وضعیت</th><th>نتیجه</th><th>مدت کل</th><th></th></tr></thead><tbody>@foreach($unlinkedCalls as $call)<tr><td><bdi>{{ $call->call_entered_at ? verta($call->call_entered_at)->format('Y/m/d H:i:s') : '—' }}</bdi></td><td class="oc-ltr">{{ $call->patient_phone }}</td><td>{{ $call->appointment_state }}</td><td>{{ $call->final_result }}</td><td class="oc-ltr">{{ $duration($call->total_duration_seconds) }}</td><td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.call', $call) }}">جزئیات</a></td></tr>@endforeach</tbody></table></div>
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
