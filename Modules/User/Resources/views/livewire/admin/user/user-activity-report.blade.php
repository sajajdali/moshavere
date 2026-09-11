<div class="user-report" dir="rtl">
    @php
        $duration = static function ($seconds) {
            $seconds = max(0, (int) $seconds);
            return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
        };
        $feedbackQuestions = collect(feedbackQuestions())->keyBy(function ($item) {
            $id = data_get($item, 'id');
            return $id instanceof \BackedEnum ? $id->value : (int) $id;
        });
        $roleNames = $user->roles->pluck('name')->implode('، ') ?: 'کاربر';
        $successfulAppointmentStatuses = [
            \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_SUCCESSFUL->value,
            \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_ATTENDED->value,
            \Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED->value,
        ];
        $successfulAppointments = collect($successfulAppointmentStatuses)->sum(fn ($status) => (int) ($appointmentCounts[$status] ?? 0));
        $callPresentation = \Modules\OnlineConsultation\Support\CallResultPresentation::class;
        $shortCallThresholdSeconds = \Illuminate\Support\Facades\Schema::hasTable('consultation_settings')
            ? max(0, (int) \Modules\OnlineConsultation\Models\ConsultationSetting::current()->ignored_short_call_minutes) * 60
            : 60;
    @endphp

    <div class="page-header report-page-header">
        <div>
            <div class="report-eyebrow"><i class="fa fa-line-chart"></i> نمای ۳۶۰ درجه کاربر</div>
            <h1 class="page-title">گزارش جامع {{ $user->full_name ?: 'کاربر شماره '.$user->id }}</h1>
            <p class="report-muted mb-0">تمام نوبت‌ها، تماس‌ها، کیف پول و بازخوردهای ثبت‌شده در یک نمای مدیریتی</p>
        </div>
        <div class="ms-auto pageheader-btn d-flex flex-wrap gap-2">
            @can('update', $user)
                <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-outline-primary"><i class="fa fa-pencil me-1"></i> ویرایش کاربر</a>
            @endcan
            <a href="{{ route('admin.user.index') }}" class="btn btn-primary"><i class="fa fa-arrow-right me-1"></i> فهرست کاربران</a>
        </div>
    </div>

    <section class="report-identity">
        <div class="report-avatar-wrap"><img src="{{ $user->getUserAvatar() }}" alt="{{ $user->full_name }}" class="report-avatar"></div>
        <div class="report-identity-main">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h2>{{ $user->full_name ?: 'بدون نام' }}</h2>
                <span class="report-pill report-pill-primary">{{ $roleNames }}</span>
                <span class="report-pill">شناسه #{{ $user->id }}</span>
            </div>
            <div class="report-contact-grid">
                <span><i class="fa fa-phone"></i><bdi>{{ $user->mobile ?: 'ثبت نشده' }}</bdi></span>
                <span><i class="fa fa-envelope"></i><bdi>{{ $user->email ?: 'ثبت نشده' }}</bdi></span>
                <span><i class="fa fa-calendar"></i>عضویت: {{ $user->created_at ? verta($user->created_at)->format('Y/m/d') : '—' }}</span>
                <span><i class="fa fa-user-md"></i>{{ $stats['patient_appointments'] }} نوبت به‌عنوان بیمار · {{ $stats['provider_appointments'] }} نوبت به‌عنوان پزشک</span>
            </div>
        </div>
        <div class="report-health">
            <span>نرخ موفقیت تماس</span>
            <strong>{{ $stats['call_success_rate'] }}٪</strong>
            <div class="report-progress"><i style="width: {{ min(100, $stats['call_success_rate']) }}%"></i></div>
        </div>
    </section>

    <div class="report-stats-grid">
        <article class="report-stat stat-blue"><span class="report-stat-icon"><i class="fa fa-calendar-check-o"></i></span><div><small>کل نوبت‌های مرتبط</small><strong>{{ number_format($stats['appointments']) }}</strong><em>{{ number_format($successfulAppointments) }} نوبت تکمیل/تأییدشده</em></div></article>
        <article class="report-stat stat-purple"><span class="report-stat-icon"><i class="fa fa-phone"></i></span><div><small>کل تماس‌ها</small><strong>{{ number_format($stats['calls']) }}</strong><em>{{ $duration($stats['talk_seconds']) }} مکالمه</em></div></article>
        <article class="report-stat stat-green"><span class="report-stat-icon"><i class="fa fa-check-circle"></i></span><div><small>تماس موفق</small><strong>{{ number_format($stats['successful_calls']) }}</strong><em>{{ $stats['call_success_rate'] }}٪ از تماس‌ها</em></div></article>
        <article class="report-stat stat-red"><span class="report-stat-icon"><i class="fa fa-phone-square"></i></span><div><small>بی‌پاسخ در زمان نوبت</small><strong>{{ number_format($stats['failed_calls']) }}</strong><em>{{ number_format($stats['early_calls']) }} تماس زودهنگام محاسبه نشده</em></div></article>
        <article class="report-stat stat-gold"><span class="report-stat-icon"><i class="fa fa-credit-card"></i></span><div><small>موجودی کیف پول</small><strong>{{ number_format($stats['wallet_balance']) }}</strong><em>تومان</em></div></article>
        <article class="report-stat stat-cyan"><span class="report-stat-icon"><i class="fa fa-comments"></i></span><div><small>پاسخ‌های نظرسنجی</small><strong>{{ number_format($stats['feedbacks']) }}</strong><em>{{ $stats['feedback_satisfaction'] }}٪ رضایت مثبت</em></div></article>
    </div>

    <nav class="report-jumpbar" aria-label="دسترسی سریع گزارش">
        <a href="#appointments"><i class="fa fa-calendar"></i> نوبت‌ها</a>
        <a href="#calls"><i class="fa fa-phone"></i> تماس‌ها</a>
        <a href="#wallet"><i class="fa fa-credit-card"></i> کیف پول</a>
        <a href="#feedbacks"><i class="fa fa-star"></i> نظرسنجی‌ها</a>
    </nav>

    <section class="card report-card" id="appointments">
        <div class="card-header report-card-header">
            <div><span class="report-section-icon blue"><i class="fa fa-calendar"></i></span><div><h3 class="card-title">تاریخچه کامل نوبت‌ها</h3><p>نوبت‌های کاربر در نقش بیمار و پزشک، همراه وضعیت و جزئیات مالی</p></div></div>
            @if($schemas['appointmentSchema'])
                <select class="form-control report-filter" wire:model.live="appointmentStatus" aria-label="فیلتر وضعیت نوبت">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach($appointmentStatuses as $status)<option value="{{ $status->value }}">{{ $status->getName() }}</option>@endforeach
                </select>
            @endif
        </div>
        <div class="card-body p-0">
            @if(!$schemas['appointmentSchema'])
                <div class="report-empty"><i class="fa fa-database"></i><strong>جدول نوبت‌ها در این سایت آماده نیست.</strong></div>
            @elseif($appointments->isEmpty())
                <div class="report-empty"><i class="fa fa-calendar-times-o"></i><strong>نوبتی با این مشخصات ثبت نشده است.</strong></div>
            @else
                <div class="table-responsive"><table class="table report-table mb-0"><thead><tr><th>نوبت</th><th>نقش کاربر</th><th>پزشک / بیمار</th><th>زمان مراجعه</th><th>نوع</th><th>وضعیت</th><th>مبلغ</th><th>پرونده</th></tr></thead><tbody>
                @foreach($appointments as $appointment)
                    @php
                        $isPatient = (int) $appointment->user_id === (int) $user->id;
                        $price = (int) ($appointment->transaction?->total_cost ?? data_get($appointment->details, \Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT.'.'.\Modules\AppointmentUser\app\Models\AppointmentUser::DETAIL_PAYMENT_PRICE.'.int', 0));
                        $appointmentUrl = route('admin.consultation.call-reports.appointment', $appointment->id);
                    @endphp
                    <tr data-href="{{ $appointmentUrl }}" tabindex="0" role="link" aria-label="مشاهده جزئیات و پرونده نوبت {{ $appointment->id }}" @class(['report-appointment-row', 'report-deleted' => $appointment->trashed()])>
                        <td><a class="report-appointment-link" href="{{ $appointmentUrl }}"><strong>#{{ $appointment->id }}</strong><small>{{ $appointment->tracking_code ?: 'بدون کد پیگیری' }}</small></a></td>
                        <td><span class="report-pill {{ $isPatient ? 'report-pill-primary' : 'report-pill-purple' }}">{{ $isPatient ? 'بیمار' : 'پزشک / ارائه‌دهنده' }}</span></td>
                        <td><strong>{{ $isPatient ? ($appointment->doctor?->full_name ?: '—') : ($appointment->user?->full_name ?: '—') }}</strong><small><bdi>{{ $isPatient ? ($appointment->doctor?->mobile ?: '—') : ($appointment->user?->mobile ?: '—') }}</bdi></small></td>
                        <td><strong><bdi>{{ $appointment->date_visit ? verta($appointment->date_visit)->format('Y/m/d H:i') : '—' }}</bdi></strong><small>{{ $appointment->start_time ? substr($appointment->start_time, 0, 5) : '—' }} تا {{ $appointment->end_time ? substr($appointment->end_time, 0, 5) : '—' }}</small></td>
                        <td><span class="report-pill">{{ $appointment->kind?->getName() ?? '—' }}</span><small>{{ $appointment->service?->title ?: $appointment->place?->title }}</small></td>
                        <td><span class="badge {{ $appointment->status?->getBadgeColor() }}">{{ $appointment->status?->getName() ?? '—' }}</span>@if($appointment->hasFinalizedPatientNoShow())<small class="text-danger d-block">عدم حضور بیمار؛ تسویه کامل بدون بازگشت وجه</small>@endif@if($appointment->trashed())<small class="text-danger">حذف‌شده</small>@endif</td>
                        <td><strong>{{ number_format($price) }}</strong><small>تومان</small></td>
                        <td><a class="btn btn-sm btn-outline-primary report-appointment-action" href="{{ $appointmentUrl }}"><i class="fa fa-folder-open me-1"></i> جزئیات و پرونده</a></td>
                    </tr>
                @endforeach
                </tbody></table></div>
                <div class="report-pagination">{{ $appointments->links() }}</div>
            @endif
        </div>
    </section>

    <section class="card report-card" id="calls">
        <div class="card-header report-card-header">
            <div><span class="report-section-icon purple"><i class="fa fa-phone"></i></span><div><h3 class="card-title">ریز تماس‌ها و وضعیت اتصال</h3><p>تماس‌های نوبت، تماس‌های منطبق با موبایل و تماس‌های اپراتوری کاربر بدون دوباره‌شماری</p></div></div>
            @if($schemas['callSchema'])<div class="report-filters">
                <select class="form-control report-filter" wire:model.live="callResult" aria-label="فیلتر نتیجه تماس"><option value="">همه نتایج</option>@foreach($callResultLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                <select class="form-control report-filter" wire:model.live="callDirection" aria-label="فیلتر جهت تماس"><option value="">ورودی و خروجی</option><option value="INBOUND">ورودی</option><option value="OUTBOUND">خروجی</option></select>
            </div>@endif
        </div>
        <div class="report-call-summary">
            <div><span class="dot success"></span><b>{{ $stats['successful_calls'] }}</b> موفق</div>
            <div><span class="dot danger"></span><b>{{ $stats['failed_calls'] }}</b> بی‌پاسخ در موعد</div>
            <div><span class="dot purple"></span><b>{{ $duration($stats['talk_seconds']) }}</b> مجموع مکالمه</div>
            <div class="summary-progress"><span style="width: {{ min(100, $stats['call_success_rate']) }}%"></span></div>
        </div>
        <div class="card-body p-0">
            @if(!$schemas['callSchema'])
                <div class="report-empty"><i class="fa fa-database"></i><strong>گزارش تماس در این سایت فعال نشده است.</strong></div>
            @elseif($calls->isEmpty())
                <div class="report-empty"><i class="fa fa-phone-square"></i><strong>تماسی با فیلتر انتخاب‌شده پیدا نشد.</strong></div>
            @else
                <div class="table-responsive"><table class="table report-table mb-0"><thead><tr><th>زمان / شناسه</th><th>نقش و جهت</th><th>شماره / مقصد</th><th>نوبت</th><th>نتیجه</th><th>انتظار</th><th>مکالمه</th><th>قطع تماس</th></tr></thead><tbody>
                @foreach($calls as $call)
                    @php
                        $patientRole = in_array($call->appointment_id, $patientAppointmentIds->all(), false) || (!$call->appointment_id && preg_replace('/\D+/', '', (string) $call->patient_phone) === preg_replace('/\D+/', '', (string) $user->mobile));
                        $resultOk = $call->final_result === 'ANSWERED';
                        $earlyCall = $call->isEarlyCall();
                        $displayResult = $callPresentation::label($call, $shortCallThresholdSeconds);
                        $resultTone = $callPresentation::tone($call, $shortCallThresholdSeconds);
                        $completedHangup = ! $earlyCall && $call->isCompletedConsultantHangup($shortCallThresholdSeconds);
                    @endphp
                    <tr>
                        <td><strong><bdi>{{ $call->call_entered_at ? verta($call->call_entered_at)->format('Y/m/d H:i:s') : verta($call->created_at)->format('Y/m/d H:i:s') }}</bdi></strong><small class="ltr">{{ $call->call_id }}</small></td>
                        <td><span class="report-pill {{ $patientRole ? 'report-pill-primary' : 'report-pill-purple' }}">{{ $patientRole ? 'بیمار' : 'پزشک / اپراتور' }}</span><small>{{ $call->direction === 'OUTBOUND' ? 'خروجی' : ($call->direction === 'INBOUND' ? 'ورودی' : 'نامشخص') }}</small></td>
                        <td><strong class="ltr">{{ $call->patient_phone ?: '—' }}</strong><small class="ltr">مقصد: {{ $call->connected_destination ?: $call->primary_extension ?: $call->destination ?: '—' }}</small></td>
                        <td>@if($call->appointment_id)<strong>#{{ $call->appointment_id }}</strong><small>{{ $call->appointment?->tracking_code ?: '—' }}</small>@else<span class="report-pill">بدون نوبت مرتبط</span>@endif</td>
                        <td><span class="report-result is-{{ $resultTone }}"><i class="fa {{ $resultOk ? 'fa-check' : ($earlyCall ? 'fa-clock-o' : 'fa-times') }}"></i>{{ $displayResult }}</span><small>{{ ['DIRECT'=>'مستقیم','DIVERTED'=>'انتقال‌یافته','NONE'=>'بدون اتصال'][$call->connection_type] ?? $call->connection_type }}</small>@if($call->surveyScore() !== null)<small><i class="fa fa-star" aria-hidden="true"></i> نظرسنجی: {{ $call->surveyScore() }} از ۵</small>@endif</td>
                        <td><bdi>{{ $duration($call->wait_duration_seconds + $call->ring_duration_seconds) }}</bdi></td>
                        <td><strong class="report-talk"><bdi>{{ $duration($call->talk_duration_seconds) }}</bdi></strong></td>
                        <td>{{ $earlyCall ? 'خارج از بازه نوبت' : ($completedHangup ? 'پایان عادی توسط پزشک' : (['PATIENT'=>'بیمار','DOCTOR'=>'پزشک','SYSTEM'=>'سیستم','UNKNOWN'=>'نامشخص'][$call->disconnected_by] ?? '—')) }}<small>{{ $earlyCall ? 'جزو بی‌پاسخ نیست' : ($completedHangup ? 'مشاوره بیشتر از حد تنظیم‌شده انجام شده' : 'علت: '.($call->hangup_cause ?? '—') ) }}</small></td>
                    </tr>
                @endforeach
                </tbody></table></div>
                <div class="report-pagination">{{ $calls->links() }}</div>
            @endif
        </div>
    </section>

    <section class="card report-card" id="wallet">
        <div class="card-header report-card-header"><div><span class="report-section-icon gold"><i class="fa fa-credit-card"></i></span><div><h3 class="card-title">کیف پول و گردش مالی</h3><p>موجودی لحظه‌ای بر اساس آخرین سند و تمام افزایش‌ها و برداشت‌ها</p></div></div></div>
        <div class="report-wallet-banner">
            <div><small>موجودی فعلی</small><strong>{{ number_format($stats['wallet_balance']) }} <em>تومان</em></strong></div>
            <div class="wallet-flow positive"><i class="fa fa-arrow-down"></i><span>کل ورودی<strong>+{{ number_format($stats['wallet_credits']) }}</strong></span></div>
            <div class="wallet-flow negative"><i class="fa fa-arrow-up"></i><span>کل خروجی<strong>-{{ number_format($stats['wallet_debits']) }}</strong></span></div>
        </div>
        <div class="card-body p-0">
            @if(!$schemas['walletSchema'])
                <div class="report-empty"><i class="fa fa-database"></i><strong>کیف پول در این سایت راه‌اندازی نشده است.</strong></div>
            @elseif($walletEntries->isEmpty())
                <div class="report-empty"><i class="fa fa-credit-card"></i><strong>هنوز گردشی برای کیف پول ثبت نشده است.</strong></div>
            @else
                <div class="table-responsive"><table class="table report-table mb-0"><thead><tr><th>زمان / سند</th><th>نوع عملیات</th><th>تغییر موجودی</th><th>قبل</th><th>بعد</th><th>مرجع و توضیحات</th></tr></thead><tbody>
                @foreach($walletEntries as $entry)
                    @php
                        $walletType = $entry->type instanceof \BackedEnum ? $entry->type->value : (string) $entry->type;
                    @endphp
                    <tr><td><strong><bdi>{{ verta($entry->created_at)->format('Y/m/d H:i:s') }}</bdi></strong><small>#{{ $entry->id }}</small></td><td><span class="report-pill">{{ $walletTypeLabels[$walletType] ?? $walletType }}</span></td><td><strong class="{{ $entry->amount_change >= 0 ? 'amount-positive' : 'amount-negative' }}">{{ $entry->amount_change >= 0 ? '+' : '' }}{{ number_format($entry->amount_change) }}</strong><small>تومان</small></td><td>{{ number_format($entry->balance_before) }}<small>تومان</small></td><td><strong>{{ number_format($entry->balance_after) }}</strong><small>تومان</small></td><td><strong>{{ $entry->transaction_id ? 'تراکنش #'.$entry->transaction_id : (data_get($entry->detail, 'appointment_id') ? 'نوبت #'.data_get($entry->detail, 'appointment_id') : 'ثبت سیستمی') }}</strong><small>{{ data_get($entry->detail, 'reason') ?: data_get($entry->detail, 'appointment_tracking_code') ?: ($entry->idempotency_key ?: '—') }}</small></td></tr>
                @endforeach
                </tbody></table></div>
                <div class="report-pagination">{{ $walletEntries->links() }}</div>
            @endif
        </div>
    </section>

    <section class="card report-card" id="feedbacks">
        <div class="card-header report-card-header"><div><span class="report-section-icon cyan"><i class="fa fa-star"></i></span><div><h3 class="card-title">نظرسنجی‌ها و تجربه کاربر</h3><p>تمام پاسخ‌های ثبت‌شده برای نوبت‌های این کاربر</p></div></div><span class="report-pill report-pill-primary">رضایت مثبت: {{ $stats['feedback_satisfaction'] }}٪</span></div>
        <div class="card-body">
            @if(!$schemas['feedbackSchema'] || !$schemas['appointmentSchema'])
                <div class="report-empty"><i class="fa fa-database"></i><strong>اطلاعات نظرسنجی در این سایت در دسترس نیست.</strong></div>
            @elseif($feedbacks->isEmpty())
                <div class="report-empty"><i class="fa fa-commenting-o"></i><strong>این کاربر هنوز نظرسنجی ثبت نکرده است.</strong></div>
            @else
                <div class="feedback-grid">
                    @foreach($feedbacks as $feedback)
                        @php
                            $questionData = $feedbackQuestions->get((int) $feedback->question);
                            $question = data_get($questionData, 'question') ?: (\Modules\Front\enum\FeedbackId::tryFrom((int) $feedback->question)?->getQuestion() ?? 'پرسش شماره '.$feedback->question);
                            $choices = data_get($questionData, 'choises', data_get($questionData, 'choices', \Modules\Front\enum\FeedbackId::tryFrom((int) $feedback->question)?->getQuestionChoises() ?? []));
                            $answer = $choices[(int) $feedback->answer] ?? 'گزینه '.$feedback->answer;
                            $positive = in_array((int) $feedback->answer, [0, 1, 2], true);
                        @endphp
                        <article class="feedback-item {{ $positive ? 'positive' : 'negative' }}">
                            <div class="feedback-top"><span><i class="fa {{ $positive ? 'fa-smile-o' : 'fa-frown-o' }}"></i>{{ $positive ? 'رضایت مثبت' : 'بازخورد منفی' }}</span><time><bdi>{{ verta($feedback->created_at)->format('Y/m/d H:i') }}</bdi></time></div>
                            <h4>{{ $question }}</h4><strong>{{ trim($answer) }}</strong>
                            <footer><span>نوبت #{{ $feedback->appointment_user_id }}</span><span>پزشک: {{ $feedback->appointmentUser?->doctor?->full_name ?: '—' }}</span></footer>
                        </article>
                    @endforeach
                </div>
                <div class="report-pagination px-0 pb-0">{{ $feedbacks->links() }}</div>
            @endif
        </div>
    </section>

    @if($appointmentStatus !== '' || $callResult !== '' || $callDirection !== '')
        <button type="button" class="btn btn-secondary report-reset" wire:click="resetFilters"><i class="fa fa-refresh"></i> پاک‌کردن همه فیلترها</button>
    @endif

    <style>
        .user-report { --rp:#2563eb; --rp2:#7c3aed; --rg:#059669; --rr:#dc2626; --rtext:#172033; --rmuted:#697386; --rborder:#e8edf5; color:var(--rtext); }
        .report-page-header { align-items:flex-end; margin-bottom:18px; }
        .report-eyebrow { color:var(--rp); font-size:12px; font-weight:800; letter-spacing:.04em; margin-bottom:7px; }
        .report-muted { color:var(--rmuted); }
        .report-identity { position:relative; overflow:hidden; display:grid; grid-template-columns:auto 1fr 190px; align-items:center; gap:20px; padding:24px; margin-bottom:18px; background:linear-gradient(125deg,#111c3c 0%,#1e3a8a 62%,#2563eb 100%); border-radius:18px; color:#fff; box-shadow:0 14px 35px rgba(30,58,138,.18); }
        .report-identity:after { content:""; position:absolute; width:260px; height:260px; left:-80px; top:-140px; border:42px solid rgba(255,255,255,.06); border-radius:50%; }
        .report-avatar-wrap { padding:4px; background:rgba(255,255,255,.18); border-radius:50%; z-index:1; }
        .report-avatar { display:block; width:82px; height:82px; object-fit:cover; border-radius:50%; border:3px solid #fff; }
        .report-identity-main { z-index:1; }.report-identity h2 { color:#fff; margin:0; font-size:22px; }
        .report-contact-grid { display:flex; flex-wrap:wrap; gap:10px 22px; margin-top:14px; color:#dbeafe; font-size:12px; }.report-contact-grid i { margin-left:6px; color:#93c5fd; }
        .report-health { z-index:1; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.14); border-radius:14px; padding:15px; }.report-health span { font-size:11px; color:#dbeafe; }.report-health strong { display:block; font-size:27px; color:#fff; margin:3px 0 8px; }
        .report-progress,.summary-progress { height:6px; overflow:hidden; background:rgba(255,255,255,.15); border-radius:10px; }.report-progress i,.summary-progress span { display:block; height:100%; border-radius:10px; background:linear-gradient(90deg,#34d399,#a7f3d0); }
        .report-pill { display:inline-flex; align-items:center; padding:5px 9px; border-radius:20px; color:#526078; background:#eef2f7; font-size:10px; font-weight:700; white-space:nowrap; }.report-pill-primary { color:#1d4ed8; background:#dbeafe; }.report-pill-purple { color:#6d28d9; background:#ede9fe; }.report-identity .report-pill { color:#fff; background:rgba(255,255,255,.14); }
        .report-stats-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:12px; margin-bottom:18px; }.report-stat { display:flex; align-items:center; gap:11px; min-height:112px; padding:16px; background:#fff; border:1px solid var(--rborder); border-radius:15px; box-shadow:0 5px 18px rgba(30,41,59,.04); }.report-stat-icon { flex:0 0 40px; width:40px; height:40px; display:grid; place-items:center; border-radius:12px; font-size:17px; }.report-stat small,.report-stat em { display:block; color:var(--rmuted); font-size:10px; font-style:normal; }.report-stat strong { display:block; margin:3px 0; font-size:21px; line-height:1.2; }.stat-blue .report-stat-icon{background:#dbeafe;color:#2563eb}.stat-purple .report-stat-icon{background:#ede9fe;color:#7c3aed}.stat-green .report-stat-icon{background:#d1fae5;color:#059669}.stat-red .report-stat-icon{background:#fee2e2;color:#dc2626}.stat-gold .report-stat-icon{background:#fef3c7;color:#d97706}.stat-cyan .report-stat-icon{background:#cffafe;color:#0891b2}
        .report-jumpbar { position:sticky; top:8px; z-index:8; display:flex; justify-content:center; gap:7px; width:max-content; max-width:100%; margin:0 auto 18px; padding:7px; background:rgba(255,255,255,.92); backdrop-filter:blur(10px); border:1px solid var(--rborder); border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,.08); }.report-jumpbar a { padding:8px 13px; border-radius:9px; color:#4b5563; font-size:11px; font-weight:700; }.report-jumpbar a:hover { color:#fff; background:var(--rp); }.report-jumpbar i { margin-left:5px; }
        .report-card { scroll-margin-top:80px; overflow:hidden; margin-bottom:18px; border:1px solid var(--rborder); border-radius:16px; box-shadow:0 6px 22px rgba(30,41,59,.045); }.report-card-header { display:flex; align-items:center; justify-content:space-between; gap:15px; padding:17px 20px; background:#fff; border-bottom:1px solid var(--rborder); }.report-card-header>div:first-child { display:flex; align-items:center; gap:12px; }.report-card-header h3 { margin:0 0 4px; }.report-card-header p { margin:0; color:var(--rmuted); font-size:10px; }.report-section-icon { display:grid; place-items:center; width:38px; height:38px; border-radius:11px; font-size:16px; }.report-section-icon.blue{color:#2563eb;background:#dbeafe}.report-section-icon.purple{color:#7c3aed;background:#ede9fe}.report-section-icon.gold{color:#d97706;background:#fef3c7}.report-section-icon.cyan{color:#0891b2;background:#cffafe}
        .report-filters { display:flex!important; gap:8px!important; }.report-filter { min-width:155px; max-width:220px; height:38px; font-size:11px; }
        .report-table { color:var(--rtext); }.report-table thead th { padding:12px 14px; border:0; background:#f8fafc; color:#697386; font-size:10px; font-weight:800; white-space:nowrap; }.report-table tbody td { padding:13px 14px; border-color:#eff3f8; vertical-align:middle; font-size:11px; }.report-table tbody tr:hover { background:#fafcff; }.report-table td>small { display:block; margin-top:4px; color:#8791a4; font-size:9px; }.report-table .ltr { direction:ltr; text-align:right; }.report-deleted { opacity:.72; background:#fff8f8; }.report-appointment-row{cursor:pointer}.report-appointment-row:focus{outline:2px solid var(--rp);outline-offset:-2px;background:#f5f8ff}.report-appointment-link{display:block;color:inherit}.report-appointment-link:hover{color:var(--rp)}.report-appointment-link small{display:block;margin-top:4px;color:#8791a4;font-size:9px}.report-appointment-action{white-space:nowrap}
        .report-result { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; border-radius:8px; font-size:10px; font-weight:800; }.report-result.is-success{color:#047857;background:#d1fae5}.report-result.is-danger{color:#b42318;background:#fee4e2}.report-result.is-warning{color:#9a6700;background:#fff3cd}.report-result.is-info{color:#175cd3;background:#eaf2ff}.report-result.is-purple{color:#6941c6;background:#f1ebff}.report-result.is-muted{color:#5f6c7b;background:#eef1f4}.report-talk{color:#6d28d9}.amount-positive{color:#047857}.amount-negative{color:#b91c1c}
        .report-call-summary { display:grid; grid-template-columns:auto auto auto 1fr; align-items:center; gap:22px; padding:13px 20px; background:#fafbff; border-bottom:1px solid var(--rborder); font-size:11px; }.report-call-summary>div { display:flex; align-items:center; gap:6px; }.report-call-summary b { font-size:14px; }.dot { width:8px;height:8px;border-radius:50%}.dot.success{background:#10b981}.dot.danger{background:#ef4444}.dot.purple{background:#8b5cf6}.summary-progress{min-width:100px;background:#e8eaf2}.summary-progress span{background:linear-gradient(90deg,#8b5cf6,#2563eb)}
        .report-wallet-banner { display:grid; grid-template-columns:1.5fr 1fr 1fr; gap:1px; background:#e8edf5; border-bottom:1px solid var(--rborder); }.report-wallet-banner>div { padding:20px 24px; background:linear-gradient(135deg,#fffbeb,#fff); }.report-wallet-banner small { display:block; color:#8a6d25; }.report-wallet-banner>div>strong { display:block; margin-top:5px; color:#92400e; font-size:25px; }.report-wallet-banner em { font-size:11px;font-style:normal}.wallet-flow { display:flex; align-items:center; gap:11px!important;background:#fff!important}.wallet-flow>i { width:36px;height:36px;display:grid;place-items:center;border-radius:50%}.wallet-flow span{font-size:10px;color:var(--rmuted)}.wallet-flow strong{display:block;font-size:17px;margin-top:4px}.wallet-flow.positive>i{color:#059669;background:#d1fae5}.wallet-flow.positive strong{color:#059669}.wallet-flow.negative>i{color:#dc2626;background:#fee2e2}.wallet-flow.negative strong{color:#dc2626}
        .feedback-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }.feedback-item { padding:16px; border:1px solid var(--rborder); border-right:4px solid #10b981; border-radius:12px; background:#fff; }.feedback-item.negative { border-right-color:#ef4444;background:#fffafa}.feedback-top,.feedback-item footer { display:flex;justify-content:space-between;gap:12px}.feedback-top span { color:#047857;font-size:10px;font-weight:800}.feedback-item.negative .feedback-top span{color:#b91c1c}.feedback-top time,.feedback-item footer{color:var(--rmuted);font-size:9px}.feedback-item h4{margin:13px 0 7px;font-size:12px}.feedback-item>strong{font-size:15px}.feedback-item footer{margin-top:15px;padding-top:10px;border-top:1px dashed var(--rborder)}
        .report-empty { display:flex;flex-direction:column;align-items:center;gap:10px;padding:42px;color:#788397;text-align:center}.report-empty i{font-size:28px;color:#c2cad7}.report-pagination{padding:14px 18px;border-top:1px solid var(--rborder)}.report-reset{position:fixed;left:24px;bottom:24px;z-index:10;box-shadow:0 8px 25px rgba(15,23,42,.18)}
        @media(max-width:1200px){.report-stats-grid{grid-template-columns:repeat(3,1fr)}}
        @media(max-width:768px){.report-identity{grid-template-columns:auto 1fr}.report-health{grid-column:1/-1}.report-stats-grid{grid-template-columns:repeat(2,1fr)}.report-card-header{align-items:flex-start;flex-direction:column}.report-filters{width:100%}.report-filter{min-width:0;max-width:none;flex:1}.report-call-summary{grid-template-columns:repeat(3,1fr)}.summary-progress{grid-column:1/-1}.report-wallet-banner{grid-template-columns:1fr}.feedback-grid{grid-template-columns:1fr}.report-jumpbar{overflow:auto;justify-content:flex-start}.report-jumpbar a{white-space:nowrap}}
        @media(max-width:480px){.report-identity{grid-template-columns:1fr;text-align:center}.report-avatar-wrap{margin:auto}.report-identity-main .d-flex{justify-content:center}.report-contact-grid{justify-content:center}.report-stats-grid{grid-template-columns:1fr}.report-filters{flex-direction:column!important}.report-call-summary{grid-template-columns:1fr 1fr}.summary-progress{grid-column:1/-1}}
    </style>
    <script>
        if (!window.userReportAppointmentNavigationBound) {
            window.userReportAppointmentNavigationBound = true;
            document.addEventListener('click', function (event) {
                const row = event.target.closest('.report-appointment-row[data-href]');
                if (!row || event.target.closest('a, button, input, select, textarea, label')) return;
                window.location.href = row.dataset.href;
            });
            document.addEventListener('keydown', function (event) {
                const row = event.target.closest('.report-appointment-row[data-href]');
                if (!row || (event.key !== 'Enter' && event.key !== ' ')) return;
                event.preventDefault();
                window.location.href = row.dataset.href;
            });
        }
    </script>
</div>
