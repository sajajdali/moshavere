@extends('onlineconsultation::shell')
@section('consultation-title', 'داشبورد مشاوران تلفنی')
@section('consultation-description', 'آمار روزانه و ماهانه نوبت‌ها، تماس‌ها و مبالغ مشاوران فعال')
@section('consultation-header-actions')
<a class="oc-btn" href="{{ route('admin.consultation.consultants-dashboard.export', $mode==='monthly' ? ['period'=>'month','month'=>$selectedMonth] : ['period'=>'custom','from'=>$selectedDate,'to'=>$selectedDate]) }}"><i class="fa-solid fa-file-export"></i>گزارش کلی مرکز</a>
@endsection
@section('consultation-content')
@php
    $duration = fn ($seconds) => sprintf(
        '%d ساعت و %02d دقیقه',
        intdiv((int) $seconds, 3600),
        intdiv(((int) $seconds) % 3600, 60)
    );
@endphp
<div class="oc-stack">
<section class="oc-panel oc-period-panel">
    <div class="oc-period-tabs">
        <a class="oc-period-tab {{ $mode==='daily'?'is-active':'' }}" href="{{ route('admin.consultation.consultants-dashboard.index',['mode'=>'daily','date'=>verta()->format('Y/m/d')]) }}"><i class="fa-regular fa-calendar-day"></i>گزارش روزانه</a>
        <a class="oc-period-tab {{ $mode==='monthly'?'is-active':'' }}" href="{{ route('admin.consultation.consultants-dashboard.index',['mode'=>'monthly','year'=>$selectedYear,'month'=>$selectedMonthNumber]) }}"><i class="fa-regular fa-calendar-days"></i>گزارش ماهانه</a>
    </div>
    @if($mode==='daily')
    <div class="oc-day-navigator">
        <a class="oc-nav-arrow" title="روز قبل" href="{{ route('admin.consultation.consultants-dashboard.index',['mode'=>'daily','date'=>$previousDate]) }}"><i class="fa-solid fa-chevron-right"></i><span>روز قبل</span></a>
        <form method="GET" class="oc-date-picker-form"><input type="hidden" name="mode" value="daily"><label for="dashboard-date">روز گزارش</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" id="dashboard-date" data-jdp name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"></div></form>
        <a class="oc-nav-arrow {{ $selectedDate===verta()->format('Y/m/d')?'is-disabled':'' }}" title="روز بعد" href="{{ route('admin.consultation.consultants-dashboard.index',['mode'=>'daily','date'=>$nextDate]) }}"><span>روز بعد</span><i class="fa-solid fa-chevron-left"></i></a>
    </div>
    @else
    <form method="GET" class="oc-month-picker"><input type="hidden" name="mode" value="monthly"><div class="oc-field"><label class="oc-label" for="dashboard-month">ماه</label><select class="oc-input" id="dashboard-month" name="month">@foreach($monthNames as $number=>$name)<option value="{{ $number }}" @selected($selectedMonthNumber===$number)>{{ $name }}</option>@endforeach</select></div><div class="oc-field"><label class="oc-label" for="dashboard-year">سال</label><select class="oc-input" id="dashboard-year" name="year">@foreach($years as $year)<option value="{{ $year }}" @selected($selectedYear===$year)>{{ $year }}</option>@endforeach</select></div><button class="oc-btn oc-btn-primary">نمایش گزارش</button></form>
    @endif
    <div class="oc-period-summary"><i class="fa-solid fa-chart-line"></i><span>نمایش آمار <strong>{{ $mode==='daily'?'روز '.verta($from)->format('Y/m/d'):'ماه '.$monthNames[$selectedMonthNumber].' '.$selectedYear }}</strong></span></div>
</section>
@forelse($rows as $row)
    @php
        $p = $row['profile'];
        $s = $row['stats'];
    @endphp
    <a class="oc-panel oc-consultant-card" href="{{ route('admin.consultation.consultants-dashboard.show', $mode==='monthly' ? ['practitioner'=>$p,'period'=>'month','month'=>$selectedMonth] : ['practitioner'=>$p,'period'=>'custom','from'=>$selectedDate,'to'=>$selectedDate]) }}">
        <div class="oc-consultant-person"><img src="{{ $p->user->getUserAvatar() }}" alt="تصویر {{ $p->display_name }}"><div><strong>{{ $p->display_name }}</strong><span>{{ $p->specialty ?: 'مشاور تلفنی / آنلاین' }}</span></div><i class="fa-solid fa-chevron-left"></i></div>
        <div class="oc-consultant-metrics">
            @foreach([['کل نوبت',$s['total'],''],['عدم حضور بیمار',$s['patient_no_show'],'danger'],['انجام‌شده',$s['completed'],'good'],['در انتظار',$s['remaining'],''],['ازدست‌رفته',$s['missed'],'danger'],['تماس‌ها',$s['calls'],''],['پاسخ‌داده‌شده',$s['answered'],'good'],['بی‌پاسخ',$s['unanswered'],'danger'],['مدت مکالمه',$duration($s['talk_seconds']),''],['مبلغ نهایی‌شده',number_format($s['income']).' تومان',''],['مبلغ نیازمند تأیید',number_format($s['pending_income']).' تومان',''],['بازگشت قطعی',number_format($s['refunded']).' تومان','danger'],['بازگشت پیشنهادی',number_format($s['refundable']).' تومان',''],['خالص قطعی',number_format($s['net_after_refund']).' تومان',''],['سهم قطعی کارشناس',number_format($s['practitioner_income']).' تومان',''],['سود قطعی مجموعه',number_format($s['platform_profit']).' تومان','good']] as [$label,$value,$tone])
                <div class="oc-metric {{ $tone ? 'is-'.$tone : '' }}"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
            @endforeach
        </div>
    </a>
@empty
    <div class="oc-panel oc-empty"><i class="fa-solid fa-user-slash"></i><h3>مشاور فعالی وجود ندارد</h3><p>فقط افرادی نمایش داده می‌شوند که «مشاوره تلفنی یا آنلاین» برایشان فعال باشد.</p></div>
@endforelse
</div>
@endsection
@push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jalaliDatepicker)jalaliDatepicker.startWatch({zIndex:99999});});</script>@endpush
