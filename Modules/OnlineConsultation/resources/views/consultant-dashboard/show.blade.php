@extends('onlineconsultation::shell')
@section('consultation-title', 'گزارش '.$practitioner->display_name)
@section('consultation-description', 'آمار و تمام نوبت‌ها در بازه انتخاب‌شده')
@section('consultation-header-actions')<a class="oc-btn" href="{{ route('admin.consultation.consultants-dashboard.index') }}"><i class="fa-solid fa-arrow-right"></i>بازگشت</a>@endsection
@section('consultation-content')
@php
$duration=fn($s)=>sprintf('%02d:%02d:%02d',intdiv((int)$s,3600),intdiv(((int)$s)%3600,60),((int)$s)%60);
$labels=['patient_no_show'=>'عدم حضور بیمار (تسویه کامل)','pending'=>'در انتظار تماس','in_progress'=>'در حال انجام','completed'=>'انجام‌شده','patient_no_answer'=>'بیمار پاسخ نداده','practitioner_no_answer'=>'مشاور پاسخ نداده','failed'=>'تماس ناموفق','missed'=>'ازدست‌رفته','review'=>'نیازمند بررسی'];
$financial=['settled'=>'تسویه‌شده','refundable'=>'دارای مبلغ قابل بازگشت','unsettled'=>'تسویه‌نشده'];
$filterBase=\Illuminate\Support\Arr::only($filters,['period','month','from','to','search']);
$statCards=[
    ['کل نوبت',$stats['total'],'',[]],
    ['انجام‌شده',$stats['completed'],'green',['status'=>'completed']],
    ['عدم حضور بیمار',$stats['patient_no_show'],'danger',['status'=>'patient_no_show']],
    ['ازدست‌رفته',$stats['missed'],'danger',['missed'=>1]],
    ['باقی‌مانده',$stats['remaining'],'',['remaining'=>1]],
    ['کل تماس',$stats['calls'],'',['has_calls'=>1]],
    ['تماس پاسخ‌داده‌شده',$stats['answered'],'green',['call_status'=>'answered']],
    ['تماس بی‌پاسخ',$stats['unanswered'],'danger',['call_status'=>'unanswered']],
    ['بیمار منحصربه‌فرد',$stats['patients'],'',null],
    ['مجموع مکالمه',$duration($stats['talk_seconds']),'',null],
    ['میانگین مشاوره',$duration($stats['average_seconds']),'',null],
    ['مبلغ ویزیت',number_format($stats['income']).' تومان','',null],
    ['قابل بازگشت',number_format($stats['refundable']).' تومان','orange',null],
    ['بازگشت‌داده‌شده',number_format($stats['refunded']).' تومان','green',null],
    ['کل برگشتی قطعی',number_format($stats['effective_refund']).' تومان','danger',null],
    ['خالص پس از بازگشت',number_format($stats['net_after_refund']).' تومان','',null],
    ['سهم کارشناس',number_format($stats['practitioner_income']).' تومان','',null],
    ['سود مجموعه',number_format($stats['platform_profit']).' تومان','green',null],
];
@endphp
<div class="oc-stack">
<section class="oc-panel"><form class="oc-panel-body oc-dashboard-filter" method="GET">
    <div class="oc-field"><label class="oc-label">بازه زمانی</label><select class="oc-input" name="period" id="period">@foreach(['month'=>'ماهانه','today'=>'امروز','yesterday'=>'دیروز','week'=>'هفته جاری','custom'=>'بازه دلخواه'] as $v=>$l)<option value="{{ $v }}" @selected(($filters['period']??'month')===$v)>{{ $l }}</option>@endforeach</select></div>
    <div class="oc-field"><label class="oc-label">انتخاب ماه</label><select class="oc-input" name="month" onchange="this.form.querySelector('[name=period]').value='month';this.form.submit()">@foreach($monthOptions as $value=>$label)<option value="{{ $value }}" @selected(($filters['month']??'')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div class="oc-field custom-date"><label class="oc-label">از تاریخ</label><input class="oc-input" data-jdp name="from" value="{{ $filters['from']??'' }}"></div><div class="oc-field custom-date"><label class="oc-label">تا تاریخ</label><input class="oc-input" data-jdp name="to" value="{{ $filters['to']??'' }}"></div>
    <div class="oc-field"><label class="oc-label">بیمار / موبایل / کد نوبت</label><input class="oc-input" name="search" value="{{ $filters['search']??'' }}"></div>
    <div class="oc-field"><label class="oc-label">وضعیت نوبت</label><select class="oc-input" name="status"><option value="">همه</option>@foreach($labels as $v=>$l)<option value="{{ $v }}" @selected(($filters['status']??'')===$v)>{{ $l }}</option>@endforeach</select></div>
    <div class="oc-field"><label class="oc-label">وضعیت تماس</label><select class="oc-input" name="call_status"><option value="">همه</option><option value="answered" @selected(($filters['call_status']??'')==='answered')>پاسخ‌داده‌شده</option><option value="unanswered" @selected(($filters['call_status']??'')==='unanswered')>بی‌پاسخ</option></select></div>
    <div class="oc-filter-checks">@foreach(['missed'=>'ازدست‌رفته','no_successful_call'=>'بدون تماس موفق','refundable'=>'دارای مبلغ قابل بازگشت','unsettled'=>'تسویه‌نشده'] as $v=>$l)<label><input type="checkbox" name="{{ $v }}" value="1" @checked($filters[$v]??false)> {{ $l }}</label>@endforeach</div>
    <div class="oc-actions"><button class="oc-btn oc-btn-primary">اعمال فیلتر</button><button class="oc-btn" name="export" value="1"><i class="fa-solid fa-file-export"></i>خروجی مشاور</button></div>
</form></section>

<div class="oc-stats oc-dashboard-stats">
@foreach($statCards as [$l,$v,$tone,$cardFilter])
    @php($statClass = 'oc-panel oc-stat '.($tone==='danger'?'oc-stat-danger':($tone?'oc-stat-'.$tone:'')))
    @if($cardFilter !== null)
        <a class="{{ $statClass }} oc-stat-filter" href="{{ route('admin.consultation.consultants-dashboard.show', array_merge(['practitioner'=>$practitioner], $filterBase, $cardFilter)) }}" title="نمایش نوبت‌های مربوط به {{ $l }}"><div><span>{{ $l }}</span><strong>{{ $v }}</strong><small>مشاهده لیست <i class="fa-solid fa-arrow-left"></i></small></div></a>
    @else
        <div class="{{ $statClass }}"><div><span>{{ $l }}</span><strong>{{ $v }}</strong></div></div>
    @endif
@endforeach
</div>

@if($items->where('dashboard.alert',true)->isNotEmpty())<div class="oc-alert"><i class="fa-solid fa-triangle-exclamation"></i><strong>{{ $items->where('dashboard.alert',true)->count() }} نوبت گذشته بدون تماس موفق نیازمند رسیدگی است.</strong></div>@endif
<section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title">نوبت‌ها</h2><span>{{ verta($from)->format('Y/m/d') }} تا {{ verta($to)->format('Y/m/d') }}</span></div>
<div class="oc-table-wrap"><table class="oc-table oc-clickable-table"><thead><tr><th>نوبت / بیمار</th><th>زمان و مدت</th><th>تلاش بیمار / مشاور</th><th>پاسخ / بی‌پاسخ</th><th>مکالمه / باقی‌مانده</th><th>وضعیت و علت</th><th>مالی</th><th>آخرین تماس</th><th>عملیات</th></tr></thead><tbody>
@forelse($items as $a)
@php($d = array_replace([
    'alert' => false, 'reserved_minutes' => 0, 'patient_attempts' => 0,
    'practitioner_attempts' => 0, 'answered' => 0, 'unanswered' => 0,
    'talk_seconds' => 0, 'remaining_minutes' => 0, 'status' => 'pending',
    'reason' => null, 'financial' => 'unsettled', 'last_call_at' => null,
], (array) ($a->dashboard ?? [])))
@php($paidAmount = (int) ($a->billingRecord?->total_paid_amount ?? 0))
@php($isFinalized = $a->billingRecord?->refund_status === 'completed')
@php($effectiveRefund = (int) ($a->billingRecord?->refunded_amount ?? 0) + (int) ($a->billingRecord?->adjustments?->sum('amount_change') ?? 0))
@php($consultantIncome = $isFinalized ? (int) ($a->billingRecord?->practitioner_earned_amount ?? 0) : 0)
@php($platformProfit = $isFinalized ? (int) ($a->billingRecord?->platform_profit_amount ?? 0) : 0)
@php($pendingRefund = $a->billingRecord?->refund_status !== 'completed' ? (int) ($a->billingRecord?->suggested_refund_amount ?? 0) : 0)
@php($netAfterRefund = $isFinalized ? $paidAmount - $effectiveRefund : 0)
<tr class="{{ $d['alert']?'oc-row-danger':'' }}" data-href="{{ route('admin.consultation.call-reports.appointment',$a) }}"><td><strong>#{{ $a->tracking_code?:$a->id }}</strong><span class="oc-cell-sub">{{ $a->user?->fullName?:'—' }}</span><span class="oc-cell-sub oc-ltr">{{ $a->user?->mobile?:'—' }}</span></td><td>{{ verta($a->date_visit)->format('Y/m/d H:i') }}<span class="oc-cell-sub">{{ $d['reserved_minutes'] }} دقیقه</span></td><td>{{ $d['patient_attempts'] }} / {{ $d['practitioner_attempts'] }}@if($d['early_calls'])<span class="oc-cell-sub">{{ $d['early_calls'] }} تماس زودهنگام</span>@endif</td><td>{{ $d['answered'] }} / <span class="oc-text-danger">{{ $d['unanswered'] }}</span><span class="oc-cell-sub">بی‌پاسخ فقط در زمان نوبت</span></td><td class="oc-ltr">{{ $duration($d['talk_seconds']) }}<span class="oc-cell-sub">{{ $d['remaining_minutes'] }} دقیقه باقی‌مانده</span></td><td><span class="oc-badge {{ $d['alert']?'oc-badge-danger':'' }}">{{ $labels[$d['status']]??$d['status'] }}</span>@if($d['reason'])<span class="oc-cell-sub oc-text-danger">{{ $d['reason'] }}</span>@endif</td><td><span class="oc-badge {{ $isFinalized ? 'oc-badge-success' : 'oc-badge-warning' }}">{{ $isFinalized ? 'تأیید و نهایی‌شده' : 'نیازمند تأیید نهایی' }}</span><span class="oc-cell-sub">پرداخت بیمار: {{ number_format($paidAmount) }} تومان</span><span class="oc-cell-sub">بازگشت قطعی: {{ number_format($effectiveRefund) }} تومان</span><span class="oc-cell-sub">بازگشت پیشنهادی: {{ number_format($pendingRefund) }} تومان</span><span class="oc-cell-sub">خالص قطعی: {{ number_format($netAfterRefund) }} تومان</span><span class="oc-cell-sub oc-consultant-income">سهم قطعی مشاور: {{ number_format($consultantIncome) }} تومان</span><span class="oc-cell-sub">سود قطعی مجموعه: {{ number_format($platformProfit) }} تومان</span>@if(!$isFinalized)<span class="oc-cell-sub oc-text-danger">هنوز وارد جمع مالی نشده است</span>@endif @if($a->billingRecord && (int) $a->billingRecord->approved_unused_minutes !== (int) $a->billingRecord->system_unused_minutes) @php($minuteChange = (int) $a->billingRecord->approved_unused_minutes - (int) $a->billingRecord->system_unused_minutes)<span class="oc-cell-sub oc-financial-adjustment"><i class="fa-solid fa-user-doctor"></i> {{ (int) $a->billingRecord->approved_by === (int) $a->doctor_id ? 'پزشک' : 'مدیر' }} {{ abs($minuteChange) }} دقیقه {{ $minuteChange > 0 ? 'اضافه' : 'کم' }} کرده@if($a->billingRecord->approver) · {{ $a->billingRecord->approver->fullName }}@endif</span>@endif</td><td>{{ $d['last_call_at']?verta($d['last_call_at'])->format('Y/m/d H:i:s'):'—' }}</td><td><a class="oc-btn oc-btn-small" href="{{ route('admin.consultation.call-reports.appointment',$a) }}">جزئیات</a></td></tr>
@empty <tr><td colspan="9"><div class="oc-empty"><h3>نوبتی مطابق فیلترها پیدا نشد</h3></div></td></tr>@endforelse
</tbody></table></div></section>
</div>
@endsection
@push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jalaliDatepicker)jalaliDatepicker.startWatch({zIndex:99999});document.querySelectorAll('tr[data-href]').forEach(r=>r.addEventListener('click',e=>{if(!e.target.closest('a,button,input'))location.href=r.dataset.href}));});</script>@endpush
