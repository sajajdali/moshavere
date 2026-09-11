@extends('onlineconsultation::shell')
@section('consultation-title', 'گزارش جامع مالی کارشناسان')
@section('consultation-description', 'درآمد، بازگشت وجه، عملکرد تماس، دایورت و رضایت بیماران در یک گزارش مدیریتی')
@section('consultation-header-actions')
<button class="oc-btn" type="submit" form="financial-report-filter" name="export" value="1"><i class="fa-solid fa-file-excel"></i>خروجی اکسل CSV</button>
@endsection
@section('consultation-content')
@php
    $m = $report['allMetrics'];
    $duration = fn ($seconds) => sprintf('%02d:%02d:%02d', intdiv((int) $seconds, 3600), intdiv(((int) $seconds) % 3600, 60), ((int) $seconds) % 60);
    $money = fn ($amount) => number_format((int) $amount).' تومان';
    $callPresentation = \Modules\OnlineConsultation\Support\CallResultPresentation::class;
    $resultLabels = ['EARLY_CALL'=>'تماس پیش از زمان نوبت (خارج از بی‌پاسخ)','ANSWERED'=>'پاسخ داده‌شده','NOANSWER'=>'بدون پاسخ','BUSY'=>'مشغول / رد تماس','CALLER_ABANDONED'=>'پایان تماس قبل از اتصال','FAILED'=>'ناموفق','CHANUNAVAIL'=>'داخلی در دسترس نیست','CONGESTION'=>'اختلال شبکه','NOT_DIALED'=>'شماره‌گیری نشده','MISSING_EXTENSION'=>'داخلی ثبت نشده','UNKNOWN'=>'نامشخص'];
    $maxDailyIncome = max(1, (int) $report['daily']->max(fn ($day) => $day['metrics']['gross_income']));
    $maxDailyCalls = max(1, (int) $report['daily']->max(fn ($day) => $day['metrics']['calls']));
@endphp
<div class="oc-stack oc-financial-report">
    <section class="oc-panel">
        <form id="financial-report-filter" class="oc-panel-body oc-financial-filter" method="GET">
            <div class="oc-field"><label class="oc-label">کارشناس</label><select class="oc-input" name="practitioner_id"><option value="">همه کارشناسان</option>@foreach($practitioners as $profile)<option value="{{ $profile->id }}" @selected((int)($filters['practitioner_id']??0)===$profile->id)>{{ $profile->display_name }}{{ $profile->active?'':' (غیرفعال)' }}</option>@endforeach</select></div>
            <div class="oc-field"><label class="oc-label">وضعیت تسویه</label><select class="oc-input" name="settlement_status"><option value="all" @selected(($filters['settlement_status']??'all')==='all')>همه وضعیت‌ها</option><option value="finalized" @selected(($filters['settlement_status']??'all')==='finalized')>تأیید و نهایی‌شده</option><option value="pending" @selected(($filters['settlement_status']??'all')==='pending')>نیازمند تأیید نهایی</option></select></div>
            <div class="oc-field"><label class="oc-label">نوع بازه</label><select class="oc-input" name="period" id="financial-period">@foreach(['month'=>'ماه شمسی','today'=>'امروز','yesterday'=>'دیروز','week'=>'هفته جاری','custom'=>'بازه دلخواه'] as $value=>$label)<option value="{{ $value }}" @selected($filters['period']===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="oc-field financial-month"><label class="oc-label">ماه</label><select class="oc-input" name="month">@foreach($monthOptions as $value=>$label)<option value="{{ $value }}" @selected($filters['month']===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="oc-field financial-custom"><label class="oc-label">از تاریخ</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" data-jdp name="from" autocomplete="off" value="{{ $filters['from']??'' }}" placeholder="۱۴۰۵/۰۶/۰۱"></div></div>
            <div class="oc-field financial-custom"><label class="oc-label">تا تاریخ</label><div class="oc-date-field"><i class="fa-regular fa-calendar"></i><input class="oc-input" data-jdp name="to" autocomplete="off" value="{{ $filters['to']??'' }}" placeholder="۱۴۰۵/۰۶/۳۱"></div></div>
            <div class="oc-actions"><button class="oc-btn oc-btn-primary"><i class="fa-solid fa-filter"></i>نمایش گزارش</button><a class="oc-btn" href="{{ route('admin.consultation.financial-report.index') }}">پاک‌کردن</a></div>
        </form>
        <div class="oc-report-period"><i class="fa-regular fa-calendar-check"></i><span>بازه گزارش:</span><strong>{{ verta($from)->format('Y/m/d') }} تا {{ verta($to)->format('Y/m/d') }}</strong><span>· {{ $report['rows']->count() }} کارشناس</span></div>
    </section>

    <section class="oc-executive-grid">
        @foreach([
            ['مبلغ نهایی‌شده',$money($m['gross_income']),'فقط نوبت‌های تأیید و ثبت‌شده','fa-coins','green'],
            ['مبلغ در انتظار تأیید',$money($m['pending_gross_income']),number_format($m['unsettled']).' نوبت وارد محاسبات نشده','fa-hourglass-half','orange'],
            ['بازگشت قطعی',$money($m['refunded']),'واریز شده به بیمار','fa-rotate-left','danger'],
            ['کل هزینه برگشتی قطعی',$money($m['effective_refund']),'فقط بازگشت ثبت و نهایی‌شده','fa-money-bill-transfer','danger'],
            ['خالص پس از بازگشت',$money($m['net_after_refund']),'مبلغ ویزیت منهای کل بازگشت','fa-wallet','blue'],
            ['سهم کارشناسان',$money($m['practitioner_income']),'بر اساس زمان مالی مکالمه','fa-user-doctor','purple'],
            ['سود مجموعه',$money($m['platform_profit']),'ویزیت منهای بازگشت و سهم کارشناس','fa-building-columns','green'],
            ['بازگشت پیشنهادی',$money($m['pending_refund']),'اطلاعاتی؛ وارد محاسبات نشده','fa-clock-rotate-left','orange'],
            ['عدم حضور بیمار',number_format($m['patient_no_show']),'تسویه کامل؛ بازگشت کیف پول صفر','fa-user-xmark','orange'],
            ['کل نوبت',number_format($m['appointments']),'برای '.number_format($m['unique_patients']).' بیمار','fa-calendar-check','blue'],
            ['دقایق مالی',number_format($m['talk_minutes']),'حذف‌شده: '.number_format((int)ceil($m['ignored_talk_seconds']/60)).' دقیقه','fa-clock','blue'],
            ['نرخ پاسخ‌گویی',$m['answer_rate'].'٪',number_format($m['answered']).' پاسخ و '.number_format($m['unanswered']).' بی‌پاسخ در موعد','fa-headset','green'],
            ['رضایت بیماران',$m['survey_average']!==null?$m['survey_average'].' از ۵':'بدون داده',number_format($m['survey_appointments']).' نوبت دارای نظر','fa-star','orange'],
        ] as [$label,$value,$detail,$icon,$tone])
        <article class="oc-executive-card is-{{ $tone }}"><i class="fa-solid {{ $icon }}"></i><div><span>{{ $label }}</span><strong>{{ $value }}</strong><small>{{ $detail }}</small></div></article>
        @endforeach
    </section>

    @if($m['unsettled'])
    <div class="oc-notice oc-notice-warning"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>{{ number_format($m['unsettled']) }} نوبت نیازمند تأیید نهایی است.</strong> تا زمان ثبت تأیید و نهایی‌شدن، مبلغ بازگشت، سهم کارشناس و سود مجموعه این نوبت‌ها در جمع مالی گزارش محاسبه نمی‌شود. از فیلتر «نیازمند تأیید نهایی» برای رسیدگی استفاده کنید.</p></div>
    @endif

    @if($m['missing_payout_rate'])
    <div class="oc-notice"><i class="fa-solid fa-triangle-exclamation"></i><p>برای {{ number_format($m['missing_payout_rate']) }} صورتحساب، نرخ ساعتی سهم کارشناس ثبت نشده است؛ تا زمان ثبت نرخ در پروفایل کارشناس، سهم کارشناس صفر و سود مجموعه قطعی نیست.</p></div>
    @endif

    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-phone-volume"></i>کیفیت و مسیر تماس‌ها</h2><span>{{ number_format($m['calls']) }} تماس ثبت‌شده</span></div>
        <div class="oc-panel-body oc-call-quality-grid">
            @foreach([
                ['ورودی به مرکز',$m['inbound'],'fa-arrow-down'],['خروجی کارشناس',$m['outbound'],'fa-arrow-up'],['پاسخ داده‌شده',$m['answered'],'fa-phone'],['بی‌پاسخ در زمان نوبت',$m['unanswered'],'fa-phone-slash'],['تماس زودهنگام',$m['early_calls'],'fa-clock'],
                ['تماس کوتاه؛ قطع کارشناس',$m['consultant_hangups'],'fa-user-xmark'],['عدم پاسخ کارشناس',$m['consultant_no_answers'],'fa-bell-slash'],['دایورت‌شده',$m['diverted'],'fa-share'],['مدت دایورت',$duration($m['diverted_total_seconds']),'fa-stopwatch'],
                ['مکالمه دایورت',$duration($m['diverted_talk_seconds']),'fa-comments'],['مجموع انتظار',$duration($m['wait_seconds']),'fa-hourglass'],['مجموع زنگ',$duration($m['ring_seconds']),'fa-bell'],['میانگین مکالمه',$duration($m['average_talk_seconds']),'fa-chart-simple'],
            ] as [$label,$value,$icon])<div><i class="fa-solid {{ $icon }}"></i><span>{{ $label }}</span><strong>{{ is_numeric($value)?number_format($value):$value }}</strong></div>@endforeach
        </div>
        <div class="oc-result-breakdown">@forelse($m['call_results'] as $result=>$count)<div><span class="oc-badge oc-badge-{{ $result === 'EARLY_CALL' ? 'info' : $callPresentation::rawTone($result) }}">{{ $resultLabels[$result]??$result }}</span><strong>{{ number_format($count) }}</strong></div>@empty<span class="oc-cell-sub">هنوز نتیجه تماسی در این بازه ثبت نشده است.</span>@endforelse</div>
    </section>

    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-users"></i>مقایسه دقیق کارشناسان</h2><span>مبالغ به تومان</span></div>
        <div class="oc-table-wrap"><table class="oc-table oc-financial-table"><thead><tr><th>کارشناس</th><th>نوبت / بیمار</th><th>ورودی / خروجی</th><th>پاسخ / بی‌پاسخ</th><th>قطع کوتاه / عدم پاسخ</th><th>دایورت</th><th>زمان خام / مالی</th><th>مبلغ ویزیت</th><th>بازگشت قطعی / انتظار</th><th>کل برگشتی</th><th>خالص پس از بازگشت</th><th>سهم کارشناس</th><th>سود مجموعه</th><th>رضایت</th></tr></thead><tbody>
        @forelse($report['rows'] as $row) @php $mRow = $row['metrics']; @endphp
            <tr><td><strong>{{ $row['profile']->display_name }}</strong><small class="oc-cell-sub">{{ $row['profile']->specialty?:'کارشناس مشاوره' }}</small><small class="oc-cell-sub">نرخ سهم: {{ $money($row['profile']->payout_hourly_rate) }}</small></td><td>{{ number_format($mRow['appointments']) }} / {{ number_format($mRow['unique_patients']) }}<small class="oc-cell-sub">تکمیل: {{ $mRow['completed'] }} · باز: {{ $mRow['open'] }} · عدم حضور: {{ $mRow['patient_no_show'] }}</small></td><td>{{ $mRow['inbound'] }} / {{ $mRow['outbound'] }}</td><td><span class="oc-text-success">{{ $mRow['answered'] }} پاسخ</span><span class="oc-cell-sub oc-text-danger">{{ $mRow['unanswered'] }} بی‌پاسخ در موعد</span><small class="oc-cell-sub">{{ $mRow['early_calls'] }} زودهنگام (خارج از بی‌پاسخ)</small></td><td><span class="oc-text-danger">{{ $mRow['consultant_hangups'] }} قطع</span><small class="oc-cell-sub">{{ $mRow['consultant_no_answers'] }} عدم پاسخ</small></td><td>{{ $mRow['diverted'] }}<small class="oc-cell-sub">{{ $duration($mRow['diverted_total_seconds']) }}</small></td><td class="oc-ltr">{{ $duration($mRow['raw_talk_seconds']) }}<small class="oc-cell-sub">مالی: {{ $duration($mRow['talk_seconds']) }} · حذف: {{ $duration($mRow['ignored_talk_seconds']) }}</small></td><td>{{ number_format($mRow['gross_income']) }}</td><td class="oc-text-danger">{{ number_format($mRow['refunded']) }}<small class="oc-cell-sub">انتظار: {{ number_format($mRow['pending_refund']) }}</small></td><td class="oc-text-danger">{{ number_format($mRow['effective_refund']) }}</td><td>{{ number_format($mRow['net_after_refund']) }}</td><td class="oc-net-income">{{ number_format($mRow['practitioner_income']) }}</td><td class="oc-net-income">{{ number_format($mRow['platform_profit']) }}</td><td>{{ $mRow['survey_average']!==null?$mRow['survey_average'].' / ۵':'—' }}<small class="oc-cell-sub">{{ $mRow['survey_appointments'] }} نوبت · {{ $mRow['voice_surveys'] }} صوتی</small></td></tr>
        @empty<tr><td colspan="14"><div class="oc-empty"><i class="fa-solid fa-chart-pie"></i><h3>داده‌ای برای این بازه وجود ندارد</h3></div></td></tr>@endforelse
        </tbody></table></div>
    </section>

    <section class="oc-panel">
        <div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-receipt"></i>ریز حسابرسی نوبت‌ها</h2><span>{{ number_format($report['appointmentRows']->count()) }} نوبت · برای جزئیات روی ردیف کلیک کنید</span></div>
        <div class="oc-table-wrap"><table class="oc-table oc-financial-table oc-clickable-table"><thead><tr><th>نوبت / تاریخ</th><th>کارشناس / بیمار</th><th>رزرو / زمان مالی</th><th>تماس / پاسخ</th><th>قطع کوتاه / عدم پاسخ</th><th>دایورت</th><th>مبلغ ویزیت</th><th>بازگشت قطعی / انتظار</th><th>کل برگشتی</th><th>خالص پس از بازگشت</th><th>سهم کارشناس</th><th>سود مجموعه</th><th>نظرسنجی</th></tr></thead><tbody>
        @forelse($report['appointmentRows'] as $row) @php $a = $row['appointment']; $am = $row['metrics']; @endphp
            <tr data-href="{{ route('admin.consultation.call-reports.appointment',$a) }}"><td><strong>#{{ $a->tracking_code?:$a->id }}</strong>@if($am['patient_no_show'])<small class="oc-cell-sub oc-text-danger">{{ $a->dashboard['reason'] }}</small>@endif<small class="oc-cell-sub"><bdi>{{ $a->date_visit?verta($a->date_visit)->format('Y/m/d H:i'):'—' }}</bdi></small><span class="oc-badge {{ $am['settled'] ? 'oc-badge-success' : 'oc-badge-warning' }}">{{ $am['settled'] ? 'تأیید و نهایی‌شده' : 'نیازمند تأیید نهایی' }}</span></td><td><strong>{{ $a->doctor?->fullName?:'—' }}</strong><small class="oc-cell-sub">{{ $a->user?->fullName?:'—' }}</small><small class="oc-cell-sub oc-ltr">{{ $a->user?->mobile?:'—' }}</small></td><td>{{ $am['reserved_minutes'] }} دقیقه<small class="oc-cell-sub oc-ltr">مالی: {{ $duration($am['talk_seconds']) }}</small><small class="oc-cell-sub oc-ltr">خام: {{ $duration($am['raw_talk_seconds']) }} · حذف: {{ $duration($am['ignored_talk_seconds']) }}</small></td><td>{{ $am['calls'] }} تماس<small class="oc-cell-sub"><span class="oc-text-success">{{ $am['answered'] }} پاسخ</span> · <span class="oc-text-danger">{{ $am['unanswered'] }} بی‌پاسخ در موعد</span></small>@if($am['early_calls'])<small class="oc-cell-sub">{{ $am['early_calls'] }} زودهنگام (خارج از بی‌پاسخ)</small>@endif</td><td><span class="oc-text-danger">{{ $am['consultant_hangups'] }} قطع</span><small class="oc-cell-sub">{{ $am['consultant_no_answers'] }} عدم پاسخ</small></td><td>{{ $am['diverted'] }} تماس<small class="oc-cell-sub oc-ltr">{{ $duration($am['diverted_total_seconds']) }}</small></td><td>{{ number_format($am['all_gross_income']) }}@if($am['unsettled'])<small class="oc-cell-sub oc-text-danger">هنوز وارد جمع مالی نشده</small>@endif</td><td class="oc-text-danger">{{ number_format($am['refunded']) }}<small class="oc-cell-sub">پیشنهادی: {{ number_format($am['pending_refund']) }}</small></td><td class="oc-text-danger">{{ number_format($am['effective_refund']) }}</td><td>{{ number_format($am['net_after_refund']) }}</td><td class="oc-net-income">{{ number_format($am['practitioner_income']) }}</td><td class="oc-net-income">{{ number_format($am['platform_profit']) }}</td><td>{{ $am['survey_average']!==null?$am['survey_average'].' / ۵':'—' }}<small class="oc-cell-sub">{{ $am['survey_answers'] }} پاسخ{{ $am['voice_surveys']?' · صوتی':'' }}</small></td></tr>
        @empty<tr><td colspan="13"><div class="oc-empty"><p>نوبتی در این بازه ثبت نشده است.</p></div></td></tr>@endforelse
        </tbody></table></div>
    </section>

    <div class="oc-stack oc-report-insights">
        <section class="oc-panel oc-daily-panel">
            <div class="oc-panel-header oc-daily-heading"><div><h2 class="oc-panel-title"><i class="fa-solid fa-chart-column"></i>روند روزانه تفکیکی</h2><p class="oc-subtitle">ترکیب مالی هر روز و سهم هر بخش از مبلغ ویزیت</p></div><div class="oc-daily-legend"><span class="is-refund"><i></i>بازگشت وجه</span><span class="is-expert"><i></i>سهم کارشناس</span><span class="is-profit"><i></i>سود مجموعه</span></div></div>
            <div class="oc-daily-chart">
                @forelse($report['daily'] as $day)
                    @php
                        $dm = $day['metrics'];
                        $dayGross = max(1, (int) $dm['gross_income']);
                        $scalePercent = min(100, max(3, round($dm['gross_income'] * 100 / $maxDailyIncome, 1)));
                        $refundPercent = min(100, max(0, round($dm['effective_refund'] * 100 / $dayGross, 1)));
                        $expertPercent = min(100, max(0, round($dm['practitioner_income'] * 100 / $dayGross, 1)));
                        $profitPercent = min(100, max(0, round($dm['platform_profit'] * 100 / $dayGross, 1)));
                    @endphp
                    <article class="oc-daily-card">
                        <header class="oc-daily-card-head">
                            <div class="oc-daily-date"><i class="fa-regular fa-calendar"></i><div><strong>{{ $day['label'] }}</strong><small>{{ $dm['appointments'] }} نوبت ثبت‌شده · {{ $dm['patient_no_show'] }} عدم حضور بیمار</small></div></div>
                            <div class="oc-daily-activity"><span><i class="fa-solid fa-phone"></i>{{ number_format($dm['calls']) }} تماس</span><span><i class="fa-regular fa-clock"></i>{{ number_format($dm['talk_minutes']) }} دقیقه مالی</span><span><i class="fa-solid fa-headset"></i>{{ $dm['answer_rate'] }}٪ پاسخ‌گویی</span></div>
                            <div class="oc-daily-total"><span>مبلغ ویزیت روز</span><strong>{{ $money($dm['gross_income']) }}</strong></div>
                        </header>
                        <div class="oc-daily-composition-wrap">
                            <div class="oc-daily-composition-scale"><div style="width:{{ $scalePercent }}%"><span class="is-refund" style="width:{{ $refundPercent }}%" title="کل بازگشت: {{ $money($dm['effective_refund']) }}"></span><span class="is-expert" style="width:{{ $expertPercent }}%" title="سهم کارشناس: {{ $money($dm['practitioner_income']) }}"></span><span class="is-profit" style="width:{{ $profitPercent }}%" title="سود مجموعه: {{ $money($dm['platform_profit']) }}"></span></div></div>
                            <small>اندازه نوار نسبت به پردرآمدترین روز بازه است؛ رنگ‌ها ترکیب همان روز را نشان می‌دهند.</small>
                        </div>
                        <div class="oc-daily-finance">
                            <div class="is-gross"><i class="fa-solid fa-coins"></i><span>مبلغ ویزیت</span><strong>{{ $money($dm['gross_income']) }}</strong></div>
                            <div class="is-confirmed"><i class="fa-solid fa-circle-check"></i><span>بازگشت قطعی</span><strong>{{ $money($dm['refunded']) }}</strong></div>
                            <div class="is-warning"><i class="fa-solid fa-hourglass-half"></i><span>بازگشت در انتظار</span><strong>{{ $money($dm['pending_refund']) }}</strong></div>
                            <div class="is-refund"><i class="fa-solid fa-money-bill-transfer"></i><span>کل هزینه برگشتی</span><strong>{{ $money($dm['effective_refund']) }}</strong></div>
                            <div class="is-net"><i class="fa-solid fa-wallet"></i><span>خالص پس از بازگشت</span><strong>{{ $money($dm['net_after_refund']) }}</strong></div>
                            <div class="is-expert"><i class="fa-solid fa-user-doctor"></i><span>سهم کارشناس</span><strong>{{ $money($dm['practitioner_income']) }}</strong></div>
                            <div class="is-profit"><i class="fa-solid fa-building-columns"></i><span>سود مجموعه</span><strong>{{ $money($dm['platform_profit']) }}</strong></div>
                        </div>
                        <footer class="oc-daily-equation"><span>خالص پس از بازگشت</span><strong>{{ $money($dm['net_after_refund']) }}</strong><i class="fa-solid fa-equals"></i><span>سهم کارشناس</span><strong>{{ $money($dm['practitioner_income']) }}</strong><i class="fa-solid fa-plus"></i><span>سود مجموعه</span><strong>{{ $money($dm['platform_profit']) }}</strong></footer>
                    </article>
                @empty
                    <div class="oc-empty"><i class="fa-solid fa-chart-line"></i><h3>روند روزانه‌ای موجود نیست</h3><p>در بازه انتخاب‌شده نوبت مالی ثبت نشده است.</p></div>
                @endforelse
            </div>
        </section>
        <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-star"></i>گزارش نظرسنجی</h2></div><div class="oc-panel-body">
            <div class="oc-survey-summary"><strong>{{ $m['survey_average']!==null?$m['survey_average']:'—' }}</strong><span>میانگین از ۵</span><small>{{ $m['survey_answers'] }} پاسخ در {{ $m['survey_appointments'] }} نوبت</small><small>{{ $m['voice_surveys'] }} نظرسنجی صوتی</small></div>
            <div class="oc-survey-bars">@foreach($m['survey_distribution'] as $score=>$count) @php($percent=$m['survey_answers']?round($count*100/$m['survey_answers']):0)<div><span>{{ $score }} ستاره</span><i><b style="width:{{ $percent }}%"></b></i><strong>{{ $count }} <small>({{ $percent }}٪)</small></strong></div>@endforeach</div>
        </div></section>
    </div>
</div>
@endsection
@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jalaliDatepicker)jalaliDatepicker.startWatch({zIndex:99999});const period=document.getElementById('financial-period');const sync=()=>{document.querySelectorAll('.financial-month').forEach(el=>el.hidden=period.value!=='month');document.querySelectorAll('.financial-custom').forEach(el=>el.hidden=period.value!=='custom')};period?.addEventListener('change',sync);sync();document.querySelectorAll('tr[data-href]').forEach(row=>row.addEventListener('click',event=>{if(!event.target.closest('a,button,input'))location.href=row.dataset.href}));});</script>
@endpush
