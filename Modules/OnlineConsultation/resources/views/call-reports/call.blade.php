@extends('onlineconsultation::shell')
@section('consultation-title', 'جزئیات تماس')
@section('consultation-description', 'شناسه تماس '.$callLog->call_id)
@section('consultation-header-actions')<a class="oc-btn" href="{{ $callLog->appointment_id ? route('admin.consultation.call-reports.appointment', $callLog->appointment_id) : route('admin.consultation.call-reports.index') }}"><i class="fa-solid fa-arrow-right"></i>بازگشت</a>@endsection
@section('consultation-content')
@php
    $duration = fn ($seconds) => sprintf('%02d:%02d:%02d', intdiv((int) $seconds, 3600), intdiv(((int) $seconds) % 3600, 60), ((int) $seconds) % 60);
    $date = fn ($value) => $value ? verta($value)->format('Y/m/d H:i:s') : '—';
    $disconnectors = ['PATIENT' => 'قطع تماس توسط بیمار', 'DOCTOR' => 'قطع تماس توسط مشاور', 'SYSTEM' => 'قطع توسط سیستم', 'UNKNOWN' => 'نامشخص'];
    $early = $callLog->isEarlyCall();
    $activeHangup = ! $early && $callLog->isConsultantHangupWarning($shortCallThresholdSeconds);
    $completedHangup = ! $early && $callLog->isCompletedConsultantHangup($shortCallThresholdSeconds);
    $activeNoAnswer = $callLog->occurredDuringAppointment() && $callLog->consultantNoAnswer;
    $patientHangup = ! $early && $callLog->wasDisconnectedByPatient();
    $callPresentation = \Modules\OnlineConsultation\Support\CallResultPresentation::class;
    $displayResult = $callPresentation::label($callLog, $shortCallThresholdSeconds);
    $resultTone = $callPresentation::tone($callLog, $shortCallThresholdSeconds);
    $surveyScore = $callLog->surveyScore();
    $directionLabel = ['OUTBOUND' => 'خروجی؛ درخواست مشاور', 'INBOUND' => 'ورودی؛ تماس بیمار'][$callLog->direction] ?? 'نامشخص';
@endphp
<div class="oc-stack">
    @if($early)<div class="oc-notice"><i class="fa-solid fa-clock"></i><p><strong>{{ $displayResult }}.</strong> این تلاش خارج از بازه مشاوره است و در آمار تماس‌های بی‌پاسخ یا هشدار عدم پاسخ مشاور محاسبه نمی‌شود.</p></div>@endif
    @if($activeHangup || $activeNoAnswer)<div class="oc-alert"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>هشدار مدیریتی:</strong> {{ $activeHangup ? 'قطع تماس کوتاه توسط مشاور' : 'عدم پاسخ‌گویی مشاور در موعد نوبت' }} برای این تماس ثبت شده است.</p></div>@endif
    @if($completedHangup)<div class="oc-notice oc-notice-success"><i class="fa-solid fa-circle-check"></i><p><strong>مشاوره انجام شد.</strong> مکالمه {{ $duration($callLog->talk_duration_seconds) }} طول کشیده و از حد {{ $duration($shortCallThresholdSeconds) }} بیشتر است؛ پایان تماس توسط مشاور هشدار محسوب نمی‌شود.</p></div>@endif
    @if($patientHangup)<div class="oc-notice oc-notice-success"><i class="fa-solid fa-circle-check"></i><p><strong>تماس توسط بیمار پایان یافت.</strong> مشاوره برقرار بوده و بیمار پس از {{ $duration($callLog->talk_duration_seconds) }} مکالمه تماس را قطع کرده است؛ برای این تماس هیچ رویداد قطع مشاور ثبت نشده است.</p></div>@endif
    @if($surveyScore !== null)
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-star"></i>نظرسنجی همین تماس</h2><span class="oc-badge oc-badge-warning">{{ $surveyScore }} از ۵</span></div><div class="oc-panel-body"><div class="oc-survey-call-score" aria-label="امتیاز {{ $surveyScore }} از ۵"><strong>{{ $surveyScore }}</strong><span>از ۵</span><div aria-hidden="true">@for($star = 1; $star <= 5; $star++)<i class="fa-{{ $star <= $surveyScore ? 'solid' : 'regular' }} fa-star"></i>@endfor</div></div></div></section>
    @endif
    @if($activeHangup)
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title oc-text-danger"><i class="fa-solid fa-phone-slash"></i>قطع تماس توسط مشاور</h2><span class="oc-badge oc-badge-danger">{{ $callLog->consultantHangup->hangup_via === 'PHONE' ? 'تلفن' : 'سافت‌فون' }}</span></div><div class="oc-panel-body oc-detail-grid">
        <div><span>زمان دقیق قطع</span><strong><bdi>{{ $date($callLog->consultantHangup->hung_up_at) }}</bdi></strong></div>
        <div><span>روش قطع</span><strong>{{ $callLog->consultantHangup->hangup_via === 'PHONE' ? 'تلفن مشاور' : 'سافت‌فون مشاور' }}</strong></div>
        <div><span>داخلی</span><strong class="oc-ltr">{{ $callLog->consultantHangup->extension ?: '—' }}</strong></div>
        <div><span>کانال VoIP</span><strong class="oc-break oc-ltr">{{ $callLog->consultantHangup->channel ?: '—' }}</strong></div>
        <div><span>زمان دریافت در سامانه</span><strong><bdi>{{ $date($callLog->consultantHangup->created_at) }}</bdi></strong></div>
    </div></section>
    @endif
    @if($activeNoAnswer)
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title oc-text-danger"><i class="fa-solid fa-triangle-exclamation"></i>عدم پاسخ مشاور در موعد نوبت</h2><span class="oc-badge oc-badge-danger">هشدار مدیریتی</span></div><div class="oc-panel-body oc-detail-grid">
        <div><span>زمان ثبت عدم پاسخ</span><strong><bdi>{{ $date($callLog->consultantNoAnswer->no_answer_at) }}</bdi></strong></div>
        <div><span>شروع زنگ‌خوردن</span><strong><bdi>{{ $date($callLog->consultantNoAnswer->ring_started_at) }}</bdi></strong></div>
        <div><span>مدت زنگ‌خوردن</span><strong class="oc-ltr">{{ $duration($callLog->consultantNoAnswer->ring_duration_seconds) }}</strong></div>
        <div><span>داخلی مشاور</span><strong class="oc-ltr">{{ $callLog->consultantNoAnswer->extension ?: '—' }}</strong></div>
        <div><span>کانال VoIP</span><strong class="oc-break oc-ltr">{{ $callLog->consultantNoAnswer->channel ?: '—' }}</strong></div>
        <div><span>زمان دریافت در سامانه</span><strong><bdi>{{ $date($callLog->consultantNoAnswer->created_at) }}</bdi></strong></div>
    </div></section>
    @endif
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-phone-volume"></i>نتیجه تماس</h2><span class="oc-badge oc-badge-{{ $resultTone }}">{{ $displayResult }}</span></div><div class="oc-panel-body oc-detail-grid">
        @foreach([['شناسه فنی تماس',$callLog->call_id],['شناسه درخواست تماس',data_get($callLog->additional_data, 'request_id')],['شناسه نوبت',$callLog->appointment_id ? '#'.$callLog->appointment_id : 'بدون نوبت'],['شماره بیمار',$callLog->patient_phone],['جهت تماس',$directionLabel],['وضعیت نوبت',$early ? 'پیش از زمان نوبت' : $callLog->appointment_state],['نتیجه خام VoIP',$callLog->final_result],['نوع اتصال',$callLog->connection_type],['شروع نوبت',$date($callLog->appointment_start_at)],['پایان نوبت',$date($callLog->appointment_end_at)],['داخلی اصلی',$callLog->primary_extension],['مقصد نهایی',$callLog->destination],['مقصد متصل‌شده',$callLog->connected_destination],['شناسه کارشناس',$callLog->operator_id],['پاسخ‌گو',$callLog->operator?->fullName ?: $callLog->responded_by],['قطع‌کننده',$early ? 'خارج از بازه نوبت' : ($completedHangup ? 'پایان عادی تماس توسط مشاور' : ($activeHangup ? 'قطع تماس کوتاه توسط مشاور' : ($disconnectors[$callLog->disconnected_by] ?? $callLog->disconnected_by)))],['علت قطع',$callLog->hangup_cause]] as [$label,$value])<div><span>{{ $label }}</span><strong class="oc-break">{{ $value ?: '—' }}</strong></div>@endforeach
    </div></section>
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-timeline"></i>خط زمانی تماس</h2></div><div class="oc-panel-body"><div class="oc-timeline">
        @foreach([['ورود تماس',$callLog->call_entered_at,'fa-phone'],['شروع شماره‌گیری',$callLog->dial_started_at,'fa-phone-arrow-up-right'],['پاسخ‌گویی',$callLog->answered_at,'fa-headset'],['پایان تماس',$callLog->ended_at,'fa-phone-slash']] as [$label,$value,$icon])<div class="oc-timeline-item {{ $value ? 'is-done' : '' }}"><i class="fa-solid {{ $icon }}"></i><span>{{ $label }}</span><strong><bdi>{{ $date($value) }}</bdi></strong></div>@endforeach
    </div><div class="oc-duration-grid">@foreach([['مدت انتظار',$callLog->wait_duration_seconds],['مدت زنگ‌خوردن',$callLog->ring_duration_seconds],['مدت مکالمه واقعی',$callLog->talk_duration_seconds],['مدت کل تماس',$callLog->total_duration_seconds]] as [$label,$value])<div><span>{{ $label }}</span><strong class="oc-ltr">{{ $duration($value) }}</strong></div>@endforeach</div></div></section>
    <section class="oc-panel"><div class="oc-panel-header"><h2 class="oc-panel-title"><i class="fa-solid fa-route"></i>تلاش‌های اتصال</h2></div>@if(empty($callLog->attempts))<div class="oc-empty"><i class="fa-solid fa-route"></i><h3>مسیر اتصالی ثبت نشده است</h3></div>@else<div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>ترتیب</th><th>نوع</th><th>مقصد</th><th>شروع</th><th>پاسخ</th><th>پایان</th><th>نتیجه</th><th>زنگ</th><th>مکالمه</th></tr></thead><tbody>@foreach($callLog->attempts as $attempt)<tr><td>{{ data_get($attempt,'order','—') }}</td><td>{{ data_get($attempt,'type','—') }}</td><td class="oc-ltr">{{ data_get($attempt,'destination','—') }}</td><td><bdi>{{ $date(data_get($attempt,'dial_started_at')) }}</bdi></td><td><bdi>{{ $date(data_get($attempt,'answered_at')) }}</bdi></td><td><bdi>{{ $date(data_get($attempt,'ended_at')) }}</bdi></td><td>{{ data_get($attempt,'dial_status','—') }}</td><td class="oc-ltr">{{ $duration(data_get($attempt,'ring_duration_seconds',0)) }}</td><td class="oc-ltr">{{ $duration(data_get($attempt,'talk_duration_seconds',0)) }}</td></tr>@endforeach</tbody></table></div>@endif</section>
    @if(!empty($callLog->additional_data))<details class="oc-panel oc-json-panel"><summary><i class="fa-solid fa-code"></i>اطلاعات تکمیلی تماس</summary><pre>{{ json_encode($callLog->additional_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre></details>@endif
    @if($callLog->consultantHangup)<details class="oc-panel oc-json-panel"><summary><i class="fa-solid fa-box-archive"></i>JSON خام رویداد قطع مشاور</summary><pre>{{ json_encode($callLog->consultantHangup->raw_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre></details>@endif
    @if($callLog->consultantNoAnswer)<details class="oc-panel oc-json-panel"><summary><i class="fa-solid fa-box-archive"></i>JSON خام رویداد عدم پاسخ مشاور</summary><pre>{{ json_encode($callLog->consultantNoAnswer->raw_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre></details>@endif
    <details class="oc-panel oc-json-panel"><summary><i class="fa-solid fa-box-archive"></i>JSON خام دریافتی از سرور VoIP</summary><pre>{{ json_encode($callLog->raw_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre></details>
</div>
@endsection
