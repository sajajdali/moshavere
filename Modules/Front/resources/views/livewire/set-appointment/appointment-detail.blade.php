<div class="appointment-detail-page" dir="rtl">
    <style>
        .appointment-detail-page { --ad-primary: #2563eb; --ad-ink: #172033; --ad-muted: #68758a; --ad-line: #e7ebf2; }
        .appointment-detail-page .ad-page { min-height: calc(100vh - 80px); padding: 32px 16px 56px; background: radial-gradient(circle at 85% 0, rgba(37,99,235,.12), transparent 28rem), #f6f8fc; }
        .appointment-detail-page .ad-container { width: 100%; max-width: 880px; margin: 0 auto; }
        .appointment-detail-page .ad-hero { position: relative; overflow: hidden; padding: 18px 24px; margin-bottom: 18px; color: #fff; border-radius: 22px; background: linear-gradient(135deg, #172554 0%, #1d4ed8 58%, #38bdf8 130%); box-shadow: 0 14px 34px rgba(30,64,175,.16); }
        .appointment-detail-page .ad-hero:after { content: ''; position: absolute; width: 190px; height: 190px; left: -55px; top: -90px; border-radius: 999px; background: rgba(255,255,255,.10); }
        .appointment-detail-page .ad-title { margin: 0; font-size: clamp(19px, 3vw, 25px); line-height: 1.5; font-weight: 900; }
        .appointment-detail-page .ad-hero-meta { display: flex; margin-top: 8px; }
        .appointment-detail-page .ad-chip { display: inline-flex; align-items: center; gap: 7px; min-height: 28px; padding: 3px 10px; border: 1px solid rgba(255,255,255,.20); border-radius: 999px; background: rgba(255,255,255,.11); font-size: 11px; backdrop-filter: blur(8px); }
        .appointment-detail-page .ad-chip:before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #facc15; box-shadow: 0 0 0 4px rgba(250,204,21,.14); }
        .appointment-detail-page .ad-shell { padding: 22px; border: 1px solid rgba(226,232,240,.9); border-radius: 26px; background: rgba(255,255,255,.96); box-shadow: 0 14px 38px rgba(15,23,42,.07); }
        .appointment-detail-page .ad-alert { border-radius: 18px; box-shadow: 0 8px 22px rgba(15,23,42,.06); }
        .appointment-detail-page .ad-voip-card { position:relative;overflow:hidden;padding:26px;border:1px solid #bae6fd;border-radius:24px;background:linear-gradient(145deg,#f0f9ff 0%,#fff 58%,#ecfeff 100%);box-shadow:0 16px 36px rgba(2,132,199,.12); }
        .appointment-detail-page .ad-voip-card:before { content:'';position:absolute;width:180px;height:180px;inset-inline-end:-80px;top:-95px;border-radius:50%;background:rgba(14,165,233,.09); }
        .appointment-detail-page .ad-voip-head { position:relative;display:flex;align-items:flex-start;gap:14px; }
        .appointment-detail-page .ad-voip-icon { display:grid;place-items:center;flex:0 0 52px;width:52px;height:52px;border-radius:16px;background:linear-gradient(145deg,#0284c7,#0369a1);color:#fff;box-shadow:0 9px 22px rgba(2,132,199,.25); }
        .appointment-detail-page .ad-voip-icon svg { width:25px;height:25px;fill:currentColor; }
        .appointment-detail-page .ad-voip-kicker { margin:0;color:#0284c7;font-size:11px;font-weight:800; }
        .appointment-detail-page .ad-voip-title { margin:2px 0 0;color:#0f2942;font-size:18px;font-weight:900;line-height:1.8; }
        .appointment-detail-page .ad-voip-text { margin:13px 0 0;color:#475569;font-size:13px;line-height:2; }
        .appointment-detail-page .ad-destination-number { display:inline-flex;align-items:center;gap:5px;margin:2px 4px;padding:2px 9px;border:1px solid #7dd3fc;border-radius:9px;background:#e0f2fe;color:#075985!important;font-size:15px;font-weight:900;direction:ltr;unicode-bidi:isolate;text-decoration:none; }
        .appointment-detail-page .ad-call-important { display:flex;align-items:flex-start;gap:10px;margin-top:17px;padding:12px 14px;border:1px solid #fcd34d;border-radius:13px;background:#fffbeb;color:#78350f; }
        .appointment-detail-page .ad-call-important > i { margin-top:4px;color:#d97706;font-size:17px; }
        .appointment-detail-page .ad-call-important strong { display:block;font-size:12px;font-weight:900; }
        .appointment-detail-page .ad-call-important p { margin:1px 0 0;font-size:12px;line-height:1.9; }
        .appointment-detail-page .ad-allowed-number { display:inline-block;margin:2px 3px;padding:2px 8px;border:1px solid #fbbf24;border-radius:8px;background:#fff;color:#92400e;font-size:13px;font-weight:900;direction:ltr;unicode-bidi:isolate;font-variant-numeric:tabular-nums; }
        .appointment-detail-page .ad-countdown-title { margin:20px 0 9px;color:#64748b;font-size:11px;font-weight:700;text-align:center; }
        .appointment-detail-page .ad-voip-countdown { position:relative;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;direction:ltr; }
        .appointment-detail-page .ad-voip-countdown > div { padding:11px 5px 8px;border:1px solid #dbeafe;border-radius:14px;background:rgba(255,255,255,.92);text-align:center;box-shadow:0 5px 13px rgba(15,23,42,.04); }
        .appointment-detail-page .ad-voip-countdown strong { display:block;color:#075985;font-size:22px;font-weight:900;line-height:1.3;font-variant-numeric:tabular-nums; }
        .appointment-detail-page .ad-voip-countdown span { display:block;margin-top:2px;color:#64748b;font-size:10px;direction:rtl; }
        .appointment-detail-page .ad-voip-time { display:flex;align-items:center;justify-content:center;gap:8px;margin-top:14px;color:#334155;font-size:12px;font-weight:700; }
        .appointment-detail-page .ad-voip-ended { padding:28px 22px;border:1px solid #fde68a;border-radius:24px;background:linear-gradient(145deg,#fffbeb,#fff);text-align:center;box-shadow:0 14px 32px rgba(180,83,9,.09); }
        .appointment-detail-page .ad-voip-ended-icon { display:grid;place-items:center;width:58px;height:58px;margin:0 auto 11px;border-radius:18px;background:#fef3c7;color:#b45309;font-size:24px; }
        .appointment-detail-page .ad-voip-ended h2 { margin:0;color:#78350f;font-size:19px;font-weight:900; }
        .appointment-detail-page .ad-voip-ended p { margin:7px 0 18px;color:#78716c;font-size:13px; }
        .appointment-detail-page .ad-new-appointment { display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:46px;padding:9px 20px;border-radius:13px;background:linear-gradient(135deg,#2563eb,#0284c7);color:#fff!important;font-size:13px;font-weight:800;text-decoration:none;box-shadow:0 9px 20px rgba(37,99,235,.22); }
        .appointment-detail-page .ad-payment { padding: 20px; border: 1px solid #fed7aa; border-radius: 22px; background: linear-gradient(145deg, #fff7ed, #fff); }
        .appointment-detail-page .ad-payment-notice { border: 0 !important; border-radius: 16px !important; background: #fff !important; box-shadow: 0 5px 16px rgba(154,52,18,.07); }
        .appointment-detail-page .ad-payment-box { border: 1px solid #dbeafe !important; border-radius: 18px !important; background: #eff6ff; }
        .appointment-detail-page .ad-wallet { display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:14px;margin:14px 0;padding:16px;border:1px solid #bbf7d0;border-radius:18px;background:linear-gradient(135deg,#f0fdf4,#fff);box-shadow:0 8px 22px rgba(22,163,74,.08); }
        .appointment-detail-page .ad-wallet.is-insufficient { border-color:#fed7aa;background:linear-gradient(135deg,#fff7ed,#fff);box-shadow:0 8px 22px rgba(234,88,12,.07); }
        .appointment-detail-page .ad-wallet-icon { display:grid;place-items:center;width:48px;height:48px;border-radius:15px;background:#dcfce7;color:#15803d;font-size:20px; }
        .appointment-detail-page .ad-wallet.is-insufficient .ad-wallet-icon { background:#ffedd5;color:#c2410c; }
        .appointment-detail-page .ad-wallet-label { display:block;color:#64748b;font-size:11px;font-weight:700; }
        .appointment-detail-page .ad-wallet-balance { display:block;margin-top:2px;color:#14532d;font-size:19px;font-weight:900; }
        .appointment-detail-page .ad-wallet.is-insufficient .ad-wallet-balance { color:#9a3412; }
        .appointment-detail-page .ad-wallet-note { margin:4px 0 0;color:#64748b;font-size:11px;line-height:1.8; }
        .appointment-detail-page .ad-wallet-pay { display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:9px 17px;border:0;border-radius:13px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;font-size:12px;font-weight:900;box-shadow:0 9px 20px rgba(5,150,105,.2); }
        .appointment-detail-page .ad-payment-actions { display:flex;align-items:center;gap:9px;flex-wrap:wrap; }
        .appointment-detail-page #paymentBtn { min-height: 48px; padding-inline: 22px !important; border: 0; border-radius: 14px; background: linear-gradient(135deg, #2563eb, #0284c7); box-shadow: 0 10px 22px rgba(37,99,235,.24); }
        .appointment-detail-page #paymentBtn:hover { transform: translateY(-1px); filter: brightness(1.04); }
        .appointment-detail-page #discountBtn { display: inline-flex; padding: 7px 12px; border-radius: 10px; background: #eff6ff; text-decoration: none; }
        .appointment-detail-page .ad-section-title { align-items: center; padding-bottom: 13px; border-bottom: 1px solid var(--ad-line); font-size: 16px !important; color: var(--ad-ink); }
        .appointment-detail-page .border-card { border-color: var(--ad-line); border-radius: 18px; }
        .appointment-detail-page .ad-info-card { padding: 20px; border: 1px solid var(--ad-line); border-radius: 22px; background: #fff; }
        .appointment-detail-page .ad-doctor { align-items: center !important; padding: 14px; border: 1px solid var(--ad-line); border-radius: 16px; background: #fafcff; }
        .appointment-detail-page .ad-doctor-avatar { width: 48px; height: 48px; }
        .appointment-detail-page .ad-doctor-name { margin: 0; font-size: 14px; line-height: 1.7; color: var(--ad-ink); }
        .appointment-detail-page .ad-doctor-speciality { display: inline-flex; width: fit-content; padding: 3px 9px; font-size: 11px; color: #475569; background: #eef2f7; }
        .appointment-detail-page .ad-doctor-meta { font-size: 11px; color: var(--ad-muted); }
        .appointment-detail-page .ad-place { padding: 15px !important; font-size: 12px; background: #fff; }
        .appointment-detail-page .ad-place-title { margin-bottom: 6px !important; font-size: 13px !important; color: var(--ad-ink); }
        .appointment-detail-page .ad-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0; overflow: hidden; padding: 0 !important; border: 1px solid var(--ad-line) !important; background: #fff !important; }
        .appointment-detail-page .ad-kind { grid-column: 1 / -1; margin: 0 !important; padding: 11px 14px; border-bottom: 1px solid var(--ad-line); font-size: 12px !important; color: #1d4ed8; background: #eff6ff; }
        .appointment-detail-page .ad-facts .visit-detail { min-height: 58px; margin: 0 !important; padding: 10px 14px; border-bottom: 1px solid var(--ad-line); }
        .appointment-detail-page .ad-facts .visit-detail:nth-of-type(even) { border-left: 1px solid var(--ad-line); }
        .appointment-detail-page .visit-detail { display: flex; flex-direction: column; align-items: flex-start; gap: 3px; font-size: 12px; line-height: 1.65; color: #475569; }
        .appointment-detail-page .visit-detail strong { color: var(--ad-ink); }
        .appointment-detail-page .visit-detail object { width: 17px; height: 17px; margin-left: 5px; }
        .appointment-detail-page .visit-detail > p { margin: 0; }
        .appointment-detail-page .visit-detail > p:last-child, .appointment-detail-page .visit-detail > span { margin-right: 22px; font-size: 12px; color: #334155; }
        .appointment-detail-page .ad-full-row { grid-column: 1 / -1; }
        .appointment-detail-page iframe { min-height: 250px; }
        .appointment-detail-page button, .appointment-detail-page a { transition: all .2s ease; }
        .appointment-detail-page [wire\\:loading] > div { backdrop-filter: blur(4px); }
        @media (max-width: 640px) {
            .appointment-detail-page .ad-page { padding: 18px 10px 36px; }
            .appointment-detail-page .ad-hero { padding: 15px 17px; border-radius: 18px; }
            .appointment-detail-page .ad-shell { padding: 13px; border-radius: 20px; }
            .appointment-detail-page .ad-payment { padding: 13px; }
            .appointment-detail-page .ad-voip-card { padding:18px 14px;border-radius:19px; }
            .appointment-detail-page .ad-voip-title { font-size:15px; }
            .appointment-detail-page .ad-voip-text { font-size:12px; }
            .appointment-detail-page .ad-voip-countdown { gap:6px; }
            .appointment-detail-page .ad-voip-countdown > div { padding:9px 3px 7px;border-radius:11px; }
            .appointment-detail-page .ad-voip-countdown strong { font-size:18px; }
            .appointment-detail-page .ad-section-title { font-size: 14px !important; }
            .appointment-detail-page .ad-info-card { padding: 12px; }
            .appointment-detail-page .ad-doctor { padding: 11px; }
            .appointment-detail-page .ad-doctor-avatar { width: 44px; height: 44px; }
            .appointment-detail-page .ad-facts { grid-template-columns: 1fr; }
            .appointment-detail-page .ad-kind, .appointment-detail-page .ad-full-row { grid-column: auto; }
            .appointment-detail-page .ad-facts .visit-detail:nth-of-type(even) { border-left: 0; }
            .appointment-detail-page #paymentBtn { width: 100% !important; }
            .appointment-detail-page .ad-wallet { grid-template-columns:auto 1fr; }
            .appointment-detail-page .ad-wallet-pay { grid-column:1/-1;width:100%; }
            .appointment-detail-page .ad-payment-actions { width:100%; }
            .appointment-detail-page #paymentBtn.is-mobile-sticky { position: fixed; right: 12px; bottom: max(12px, env(safe-area-inset-bottom)); left: 12px; z-index: 60; width: auto !important; min-height: 54px; box-shadow: 0 14px 34px rgba(15,23,42,.28); animation: adPaymentBarIn .22s ease-out; }
            body.has-mobile-payment-bar { padding-bottom: 78px; }
        }
        @keyframes adPaymentBarIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <!-- Spinner Overlay -->
    <div wire:loading>
        <div class="fixed inset-0 flex items-center justify-center bg-white bg-opacity-50 z-50 ">
            <div class="animate-spin rounded-full h-32 w-32 border-t-4 border-blue-500"></div>
        </div>
    </div>
    <main class="ad-page">
        <div class="ad-container">
            @if ($fetchData['app'])
                <header class="ad-hero">
                    <h1 class="ad-title">جزئیات و پیگیری نوبت</h1>
                    <div class="ad-hero-meta">
                        <span class="ad-chip">وضعیت: {{ $fetchData['stauts']['name'] }}</span>
                    </div>
                </header>
            @endif
        @if (session()->has('success') || isset($fetchData['success']))
            <div class="ad-alert bg-emerald-100 text-emerald-800 text-base text-center py-3 px-5 mb-5 mx-auto">
                @if (session()->has('success'))
                    {{ session()->get('success') }}
                @endif
                @isset($fetchData['success'])
                    {{ $fetchData['success'] }}
                @endisset
            </div>
        @endif
        @if (isset($fetchData['alert']) || session()->has('error'))
            <div class="ad-alert bg-rose-600 text-white text-base text-center py-3 px-5 mb-5 mx-auto">
                @if (session()->has('error'))
                    {{ session()->get('error') }}
                @endif
                @isset($fetchData['alert'])
                    {{ $fetchData['alert'] }}
                @endisset
            </div>
        @endif

        <div
            class="@if ($fetchData['stauts']['enum'] == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL) bg-rose-100
       @else bg-white @endif ad-shell space-y-5">
            @if ($fetchData['monitoring'])
                <div class="border-2 border-indigo-500 bg-indigo-100  p-4 rounded-xl flex items-center gap-3 ">
                    <svg class="w-6 h-6 text-red w-10 h-10 " xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-warning" />
                    </svg>
                    <p class="text-base">
                        نوبت شما در <strong>انتظار تایید</strong> است و بعد از تایید ، وضعیت نوبت از <strong>طریق
                            پیامک</strong> به شما اطلاع رسانی میشود!
                    </p>
                </div>
            @endif
            @isset($fetchData['voipGuide'])
                @php
                    $voipGuide = $fetchData['voipGuide'];
                    $voipEnded = now()->timestamp >= $voipGuide['end_timestamp'];
                    $callCenterNumber = $voipGuide['call_center_number'] ?? null;
                    $allowedCallerNumbers = $voipGuide['allowed_caller_numbers'] ?? [];
                @endphp
                <section id="voipAppointmentGuide"
                    data-start="{{ $voipGuide['start_timestamp'] }}"
                    data-end="{{ $voipGuide['end_timestamp'] }}"
                    wire:ignore>
                    <div class="ad-voip-card" data-voip-upcoming @style(['display:none' => $voipEnded])>
                        <div class="ad-voip-head">
                            <span class="ad-voip-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M6.62 10.79a15.46 15.46 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z"/></svg>
                            </span>
                            <div>
                                <p class="ad-voip-kicker" data-voip-kicker>راهنمای ورود به اتاق مشاوره</p>
                                <h2 class="ad-voip-title" data-voip-title>برای تماس در زمان نوبت آماده باشید</h2>
                                <p class="ad-voip-text">
                                    @if($callCenterNumber)
                                        @php($callCenterTel = preg_replace('/[^0-9+]/', '', $callCenterNumber))
                                        لطفاً رأس ساعت <strong>{{ verta($fetchData['app']->date_visit)->format('H:i') }}</strong> با شماره
                                        <a class="ad-destination-number" href="tel:{{ $callCenterTel }}"><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $callCenterNumber }}</a>
                                        تماس بگیرید تا به اتاق مشاوره هدایت شوید.
                                    @else
                                        <strong class="text-rose-600">شماره مرکز تماس هنوز در تنظیمات ثبت نشده است.</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($allowedCallerNumbers !== [])
                            <div class="ad-call-important">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                <div>
                                    <strong>نکته بسیار مهم</strong>
                                    <p>فقط با شماره
                                        @foreach($allowedCallerNumbers as $allowedCallerNumber)
                                            @if(!$loop->first) یا @endif<span class="ad-allowed-number">{{ $allowedCallerNumber }}</span>
                                        @endforeach
                                        تماس بگیرید تا شماره شما شناسایی و به اتاق مشاوره هدایت شوید. با شماره دیگری تماس نگیرید؛ تماس از شماره‌های دیگر به اتاق مشاوره متصل نمی‌شود.
                                    </p>
                                </div>
                            </div>
                        @endif
                        <p class="ad-countdown-title" data-countdown-label>زمان باقی‌مانده تا شروع مشاوره</p>
                        <div class="ad-voip-countdown" aria-live="polite">
                            <div><strong data-countdown-days>۰</strong><span>روز</span></div>
                            <div><strong data-countdown-hours>۰</strong><span>ساعت</span></div>
                            <div><strong data-countdown-minutes>۰</strong><span>دقیقه</span></div>
                            <div><strong data-countdown-seconds>۰</strong><span>ثانیه</span></div>
                        </div>
                        <p class="ad-voip-time"><i class="fa-regular fa-calendar" aria-hidden="true"></i>{{ verta($fetchData['app']->date_visit)->format('Y/m/d') }}، ساعت {{ verta($fetchData['app']->date_visit)->format('H:i') }}</p>
                    </div>
                    <div class="ad-voip-ended" data-voip-ended @style(['display:none' => ! $voipEnded])>
                        <span class="ad-voip-ended-icon"><i class="fa-solid fa-hourglass-end" aria-hidden="true"></i></span>
                        <h2>زمان مشاوره شما تمام شده است</h2>
                        <p>برای انتخاب زمان دیگری می‌توانید نوبت جدید دریافت کنید.</p>
                        <a class="ad-new-appointment" href="{{ $voipGuide['new_appointment_url'] }}"><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i>دریافت نوبت جدید</a>
                    </div>
                </section>
            @endisset
            @if ($fetchData['stauts']['payment'])
                <section class="ad-payment">
                    <div class="ad-payment-notice flex flex-col gap-3 sm:flex-row items-center justify-between py-3.5 px-4 mb-4"
                        style="word-spacing: 0.08rem;">
                        <img src="{{ front_asset('assets/svg/warning-icon.svg') }}" />
                        <p class="font-bold text-sm px-4 text-gray-700 ">
                            <span>
                                نوبت شما با موفقیت <span class="text-red">رزرو شد</span>.
                                برای تایید نوبت باید مبلغ {{ number_format($fetchData['stauts']['price']) }} ریال را به
                                صورت
                                آنلاین پرداخت کنید تا نوبت شما ثبت شود و در صورت عدم
                                پرداخت نوبت شما حذف خواهد شد.
                            </span>
                            @if (setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_STATUS) != null &&
                                    setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT) != null &&
                                    !$fetchData['app']->isOnline())
                                <span>
                                    {{ setting(\Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_DETAIL_PAYMENT_DESCRIPTION_TEXT) }}
                                </span>
                            @endif
                        </p>
                        <button type="button"
                            class="font-bold text-red confirm_swal_alert text-sm cancelApp min-w-fit">لغو
                            نوبت</button>
                    </div>
                    <p class="font-bold my-3">
                        <a href="#" id="discountBtn" class="text-primary-main  mr-2">کد تخفیف دارید؟</a>
                    </p>
                    <div class="my-3" id="collapsible-content" style="display: none" wire:ignore.self>
                        <div
                            class="grid gap-4 md:grid-cols-5 items-start border-2 border-solid  border-secondary-100 rounded-2xl py-3.5 px-4  @if (isset($fetchData['status']['price_after_discount'])) opacity-40 @endif ">
                            <input type="text" id="discount-code" wire:model="form.discount_code"
                                @if (isset($fetchData['status']['price_after_discount'])) disabled @endif
                                placeholder="کد تخفیف خود را وارد کنید"
                                class="col-span-4 w-full px-4 py-2 border @error('form.discount_code') border-rose-500  @else  border-gray-300 @enderror rounded-lg focus:outline-none focus:border-success-500">
                            <button wire:click='discount' wire:target='discount' wire:loading.attr='disabled'
                                @if (isset($fetchData['status']['price_after_discount'])) disabled @endif
                                class="col-span-1   @if (isset($fetchData['status']['price_after_discount'])) bg-lime-500 @else bg-blue-500 @endif hover:bg-blue-700 text-white font-bold py-2 px-6 px-4 rounded text-center">
                                @if (isset($fetchData['status']['price_after_discount']))
                                    <span>
                                        تایید شد
                                    </span>
                                @else
                                    <span wire:loading.remove wire:target='discount'>اعمال کد</span>
                                    <div role="status" wire:loading wire:target='discount'>
                                        <svg aria-hidden="true"
                                            class="w-5 h-5 text-white-200 animate-spin dark:text-white-600 fill-gray-700"
                                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                fill="currentColor" />
                                            <path
                                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                fill="currentFill" />
                                        </svg>
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                @endif
                            </button>
                        </div>
                        @error('form.discount_code')
                            <p class="text-rose-500	 mr-2">{{ $message }}</p>
                        @enderror
                    </div>

                    @php($wallet = $fetchData['wallet'] ?? ['visible' => false])
                    @if ($wallet['visible'])
                        <div class="ad-wallet {{ $wallet['sufficient'] ? '' : 'is-insufficient' }}">
                            <span class="ad-wallet-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span>
                            <div>
                                <span class="ad-wallet-label">موجودی قابل استفاده کیف پول</span>
                                <strong class="ad-wallet-balance">{{ number_format($wallet['balance']) }} ریال</strong>
                                @if ($wallet['sufficient'])
                                    <p class="ad-wallet-note">مبلغ {{ number_format($wallet['amount']) }} ریال کسر می‌شود · موجودی پس از پرداخت: {{ number_format($wallet['balance_after']) }} ریال</p>
                                @else
                                    <p class="ad-wallet-note">برای پرداخت این نوبت {{ number_format($wallet['shortage']) }} ریال کمبود موجودی دارید.</p>
                                @endif
                            </div>
                            @if ($wallet['sufficient'])
                                <button type="button" id="walletPaymentBtn" wire:click="payWithWallet"
                                    wire:loading.attr="disabled" wire:target="payWithWallet"
                                    class="ad-wallet-pay payment-action-btn">
                                    <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
                                    <span wire:loading.remove wire:target="payWithWallet">پرداخت از کیف پول</span>
                                    <span wire:loading wire:target="payWithWallet">در حال پرداخت…</span>
                                </button>
                            @endif
                        </div>
                        @error('wallet')
                            <p class="mb-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">{{ $message }}</p>
                        @enderror
                    @endif

                    <div
                        class="ad-payment-box flex flex-col gap-3 md:flex-row items-center justify-between py-3.5 px-4">
                        @if (isset($fetchData['status']['price_after_discount']))
                            <div>
                                <p class="font-bold">
                                    جهت فعالسازی نوبت، مبلغ <s
                                        class="text-rose-500">{{ number_format($fetchData['stauts']['price']) }}</s>
                                    {{ number_format($fetchData['status']['price_after_discount']) }} ریال پرداخت
                                    نمایید
                                    @if (isset($fetchData['payment']['termAndCondition']))
                                        <div class="flex items-center mt-3" wire:ignore>
                                            <input id="termAndConditionAggrement" type="checkbox"
                                                class="form-checkbox h-3 w-3 text-blue-600" checked>
                                            <label for="termAndConditionAggrement" class="text-sm text-gray-500 mr-2">
                                                با
                                                <button id="termAndConditionModalLunch"
                                                    class="text-blue-400 hover:text-blue-700">شرایط و قوانین </button>
                                                پرداخت موافق هستم.</label>
                                        </div>
                                    @endif
                                </p>
                            </div>
                        @else
                            <div>
                                <p class="font-bold">
                                    جهت فعالسازی نوبت، مبلغ {{ number_format($fetchData['stauts']['price']) }} ریال
                                    پرداخت
                                    نمایید
                                </p>
                                @if (isset($fetchData['payment']['termAndCondition']))
                                    <div class="flex items-center mt-3" wire:ignore>
                                        <input id="termAndConditionAggrement" type="checkbox"
                                            class="form-checkbox h-3 w-3 text-blue-600" checked>
                                        <label for="termAndConditionAggrement" class="text-sm text-gray-500 mr-2"> با
                                            <button id="termAndConditionModalLunch"
                                                class="text-blue-400 hover:text-blue-700">شرایط و قوانین </button>
                                            پرداخت موافق هستم.</label>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <span id="paymentBtnSentinel" class="block h-0 w-0" aria-hidden="true"></span>
                        <div class="ad-payment-actions">
                        <button type="button" wire:click='GotoPayment' id="paymentBtn" wire:loading.attr="disabled" wire:target="GotoPayment"
                            class=" flex items-center justify-center
                             gap-3 py-2 px-4 md:px-5 bg-primary-main border-2 border-primary-main
                             text-white rounded-full
                             hover:bg-primary-tint-300 transition-colors focus:ring-2 ring-blue-sky !w-fit !px-3 payment-action-btn">
                            <p>پرداخت از درگاه بانکی</p>
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="#sprite-arrow-left-circle" />
                            </svg>
                        </button>
                        </div>
                    </div>

                </section>
            @endif
            @if ($fetchData['description'])
                <section>
                    <div class="p-4 bg-yellow/20 border-2 border-yellow border-solid rounded-2xl">
                        <div class="flex items-center gap-2 mb-3">
                            <object data="{{ front_asset('assets/svg/warning-icon-lg-yellow.svg') }}"></object>
                            <h3 class="font-bold">توضیحات مربوط به نوبت</h3>
                        </div>
                        <ul class="flex flex-col gap-2 items-stretch pr-2 text-sm">
                            {!! nl2br($fetchData['description']) !!}
                        </ul>
                    </div>
                </section>
            @endif
            @if ($fetchData['app'])
                <section>
                    <h3 class="ad-section-title text-sm font-bold mb-4 flex justify-between gap-3 flex-wrap">
                        اطلاعات نوبت شما
                        @if ($this->fetchData['stauts']['enum'] == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
                            <span
                                class="inline-flex items-center rounded-md bg-rose-400 px-2 py-1 text-xs font-semibold text-white ring-1 ring-inset ring-gray-500/10 mr-2">کنسل
                                شده</span>
                        @endif
                        @isset($fetchData['returnToApp'])
                            <a target="blank" href="{{ $fetchData['returnToApp'] }}"
                                class="bg-rose-500 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded-full">
                                <span>بازگشت به اپلیکیشن</span>
                            </a>
                        @endisset
                        @if (!disableUi() && $fetchData['app']->isOnline() && $fetchData['app']->isAppActive())
                            <a href="{{ route('front.user.chatroom', ['onlineAppId' => $fetchData['app']->online->first()->id]) }}"
                                class="bg-emerald-500 hover:bg-lime-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                                ورود به چت
                            </a>
                        @endif
                    </h3>
                    <div class="ad-info-card space-y-4">
                        <div class="ad-doctor flex flex-col md:flex-row items-stretch md:items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="ad-doctor-avatar relative bg-primary-main/50 p-0.5 rounded-full shrink-0">
                                    <span class="block w-4 h-4 bg-white p-0.5 absolute right-0 top-0">
                                        <span class="block w-full h-full rounded-full bg-green"></span>
                                    </span>
                                    <img class="w-full h-full object-cover rounded-full"
                                        src="{{ $fetchData['app']->doctor->getUserAvatar() }}" alt="doctor" />
                                </div>

                                <div class="text-sm space-y-2">
                                    <p class="ad-doctor-name font-bold">{{$fetchData['app']->doctor->speciality_type == 1 ? 'دکتر' : ''}} {{ $fetchData['app']->doctor->full_name }}</p>
                                    <p class="ad-doctor-speciality rounded-md">
                                        {{ $fetchData['app']->doctor->DocSpecialities() }}
                                    </p>
                                </div>
                            </div>
                            @if (!$fetchData['app']->kind->isOnline())
                                <div
                                    class="flex flex-row md:flex-col gap-2 justify-between md:justify-start items-center md:items-end">
                                    <a href="#" class="font-bold text-sm bg-secondary-100 px-3 py-1 rounded-2xl">
                                        تماس با مطب
                                    </a>
                                    @if (isset($fetchData['app']->doctor->dr_licence_number))
                                        <p class="ad-doctor-meta">شماره نظام
                                            پزشکی:{{ $fetchData['app']->doctor->dr_licence_number }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if (!$fetchData['app']->kind->isOnline())
                            <div class="ad-place border-card">
                                <p class="ad-place-title font-bold">{{ $fetchData['app']->place->title }}</p>
                                @if (isset($fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]))
                                    <p class="text-sm mb-4">
                                        <object class="inline-block mb-0.5"
                                            data="{{ front_asset('assets/svg/location-icon.svg') }}"></object>
                                        <span>
                                            {{ $fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                        </span>
                                    </p>
                                @endif
                                @isset($fetchData['mapUrl'])
                                    <iframe class="w-full mb-4 rounded-2xl" src="{{ $fetchData['mapUrl'] }}"
                                        width="400" height="300" style="border: 0" allowfullscreen=""
                                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                                    <a target="blank" href="{{ $fetchData['navigation'] }}"
                                        class="btn__blue--round-full-between !w-full !font-bold !text-base my-3">
                                        <span>مسیریابی</span>
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="#sprite-arrow-left-circle" />
                                        </svg>
                                    </a>
                                @endisset
                            </div>
                        @endif
                        <div class="ad-facts border-card bg-secondary-100 p-5">
                            <p class="ad-kind font-bold">نوع نوبت: {{ $fetchData['app']->kind->getName() }}</p>
                            <div class="visit-detail flex">
                                <p>
                                    <object class="inline-block"
                                        data="{{ front_asset('assets/svg/fluent-patient.svg') }}"></object>
                                    <strong>نام و نام خانوادگی مراجعه کننده:</strong>
                                </p>
                                <p class="mr-3">{{ $fetchData['app']->user->full_name }}</p>
                            </div>
                            @if (!$fetchData['app']->kind->isOnline())
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/calendar.svg') }}"></object>
                                        <strong>تاریخ نوبت:</strong>
                                    </p>
                                    <p class="mr-3">{{ verta($fetchData['app']->date_visit)->format('d F') }}</p>
                                </div>
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/timeclock.svg') }}"></object>
                                        <strong>زمان نوبت:</strong>
                                    </p>
                                    @if ($fetchData['stauts']['enum'] != Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
                                        <span class="mr-3">
                                            @if (isset($fetchData['app']->date_visit))
                                                {{ verta($fetchData['app']->date_visit)->format('H:i') }}
                                            @else
                                                ---
                                            @endif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">کنسل
                                            شده</span>
                                    @endif
                                </div>
                            @else
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/calendar.svg') }}"></object>
                                        <strong>تاریخ دریافت نوبت:</strong>
                                    </p>
                                    <p class="mr-3">{{ verta($fetchData['app']->created_at)->format('d F') }}</p>
                                </div>
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/calendar.svg') }}"></object>
                                        <strong>زمان نوبت:</strong>
                                    </p>
                                    <p class="mr-3">{{ verta($fetchData['app']->date_visit)->format('d F') }}</p>
                                </div>
                            @endif
                            @if ($fetchData['stauts']['payment'])
                                <div class="visit-detail flex mt-3">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/solar_card-outline.svg') }}"></object>
                                        <strong>مبلغ پرداختی:</strong>
                                    </p>
                                    <p class="mr-2">{{ number_format($fetchData['stauts']['price']) }} ریال</p>
                                </div>
                            @endif

                            @if (isset($fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS]) &&
                                    !$fetchData['app']->kind->isOnline())
                                <div class="ad-full-row visit-detail flex mt-3 !mb-0">
                                    <p>
                                        <object class="inline-block"
                                            data="{{ front_asset('assets/svg/location-icon.svg') }} "></object>
                                        <span>
                                            {{ $fetchData['app']->place->detail[Modules\Place\app\Models\Place::DETAIL_ADDRESS] }}
                                        </span>
                                    </p>
                                    <p></p>
                                </div>
                            @endif
                            @if ($fetchData['cancel'])
                                <div class="w-full flex justify-end">
                                    <button
                                        class=" cancelApp flex items-center justify-between gap-3 py-2 px-4 bg-red border-2  text-white rounded-full hover:bg-primary-tint-300 transition-colors">
                                        <span>کنسل کردن نوبت</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                        @if ($fetchData['stauts']['payment'])
                            <div class="h-0.5 w-full bg-secondary-100"></div>

                            <div class="text-sm space-y-2">
                                <p class="font-bold text-base !mb-4">جزئیات پرداخت</p>

                                <div class="border-card flex justify-between">
                                    <p>مبلغ قابل پرداخت</p>
                                    @if (isset($fetchData['status']['price_after_discount']))
                                        <s class="text-rose-500">
                                            <p>{{ number_format($fetchData['stauts']['price']) }} ریال</p>
                                        </s>
                                    @else
                                        <p>{{ number_format($fetchData['stauts']['price']) }} ریال</p>
                                    @endif
                                </div>
                                @if (isset($fetchData['status']['price_after_discount']))
                                    <div class="border-card flex justify-between">
                                        <p>
                                            <span>مبلغ بعد از تخفیف</span>
                                        </p>
                                        <p>{{ number_format($fetchData['status']['price_after_discount']) }} ریال</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </section>
            @endif

        </div>
        </div>
    </main>
    <input type="hidden" value="{{ $fetchData['authCheck'] }}" id="swalStatus">

    @if (isset($fetchData['payment']['termAndCondition']))
        {{-- term and condition modal --}}
        <section class="appointment__modal max-h-min" wire:ignore.self>
            <header class="appointment__modal-header  ">
                <button type="button"
                    class="bg-white border-2 border-red text-red flex items-center py-3 px-5 rounded-xl gap-3 dismissmodal">
                    <span>بستن</span>
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="#sprite-x" />
                    </svg>
                </button>
            </header>
            <main class="appointment__modal-container p-10 flex flex-column justify-between ">
                <div class="space-y-3">
                    <p>
                        {!! nl2br($fetchData['payment']['termAndCondition']) !!}
                    </p>
                </div>
            </main>
        </section>
    @endif
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            function startVoipCountdown() {
                const guide = document.getElementById('voipAppointmentGuide');
                if (!guide || guide.dataset.countdownStarted === '1') return;
                guide.dataset.countdownStarted = '1';

                const startAt = Number(guide.dataset.start) * 1000;
                const endAt = Number(guide.dataset.end) * 1000;
                const upcoming = guide.querySelector('[data-voip-upcoming]');
                const ended = guide.querySelector('[data-voip-ended]');
                const kicker = guide.querySelector('[data-voip-kicker]');
                const title = guide.querySelector('[data-voip-title]');
                const label = guide.querySelector('[data-countdown-label]');
                const output = {
                    days: guide.querySelector('[data-countdown-days]'),
                    hours: guide.querySelector('[data-countdown-hours]'),
                    minutes: guide.querySelector('[data-countdown-minutes]'),
                    seconds: guide.querySelector('[data-countdown-seconds]'),
                };
                const number = value => Number(value).toLocaleString('fa-IR', { minimumIntegerDigits: 2, useGrouping: false });
                let timer;

                const update = () => {
                    const nowAt = Date.now();
                    if (nowAt >= endAt) {
                        upcoming.style.display = 'none';
                        ended.style.display = '';
                        if (timer) window.clearInterval(timer);
                        return;
                    }

                    upcoming.style.display = '';
                    ended.style.display = 'none';
                    const consultationStarted = nowAt >= startAt;
                    const target = consultationStarted ? endAt : startAt;
                    const remaining = Math.max(0, target - nowAt);
                    const totalSeconds = Math.floor(remaining / 1000);

                    kicker.textContent = consultationStarted ? 'نوبت مشاوره شما فعال است' : 'راهنمای ورود به اتاق مشاوره';
                    title.textContent = consultationStarted ? 'همین حالا با مرکز تماس تماس بگیرید' : 'برای تماس در زمان نوبت آماده باشید';
                    label.textContent = consultationStarted ? 'زمان باقی‌مانده تا پایان نوبت' : 'زمان باقی‌مانده تا شروع مشاوره';
                    output.days.textContent = number(Math.floor(totalSeconds / 86400));
                    output.hours.textContent = number(Math.floor((totalSeconds % 86400) / 3600));
                    output.minutes.textContent = number(Math.floor((totalSeconds % 3600) / 60));
                    output.seconds.textContent = number(totalSeconds % 60);
                };

                update();
                timer = window.setInterval(update, 1000);
            }

            startVoipCountdown();
            document.addEventListener('livewire:navigated', startVoipCountdown);

            function updateMobilePaymentBar() {
                const button = document.getElementById('paymentBtn');
                const sentinel = document.getElementById('paymentBtnSentinel');
                const isMobile = window.matchMedia('(max-width: 640px)').matches;

                if (!button || !sentinel || !isMobile) {
                    button?.classList.remove('is-mobile-sticky');
                    document.body.classList.remove('has-mobile-payment-bar');
                    return;
                }

                const hasPassedButton = sentinel.getBoundingClientRect().top < 0;
                button.classList.toggle('is-mobile-sticky', hasPassedButton);
                document.body.classList.toggle('has-mobile-payment-bar', hasPassedButton);
            }

            window.addEventListener('scroll', updateMobilePaymentBar, { passive: true });
            window.addEventListener('resize', updateMobilePaymentBar);
            document.addEventListener('livewire:navigated', updateMobilePaymentBar);
            updateMobilePaymentBar();

            $('body').on('click', '#discountBtn', function() {
                var content = $('#collapsible-content');
                if (content.css('display') === 'none') {
                    content.fadeIn();
                } else {
                    content.fadeOut();
                }
            });
            $('body').on('click', '#termAndConditionModalLunch', function() {
                $('.appointment__modal').addClass('opened');
            });
            $('body').on('click', '.dismissmodal', function() {
                $('.appointment__modal').removeClass('opened');
            });
            let SAMessage = @json($fetchData['sweetAlert']['msg'] ?? false);
            let SAIcon = @json($fetchData['sweetAlert']['icon'] ?? false);
            var status = $('#swalStatus').val();
            if(SAMessage){
                Swal.fire({
                    title: 'توجه!',
                    text: SAMessage,
                    icon: SAIcon,
                    showCancelButton: false,
                    confirmButtonText: 'متوجه شدم',
                    confirmButtonColor: '#008000', // You can change the color to your preference
                });
            }
            $('body').on('click', '.cancelApp', function() {
                var status = $('#swalStatus').val();
                if (status) {
                    Swal.fire({
                        title: 'توجه!',
                        text: 'از کنسل کردن نوبت مطمعن هستید؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'بله کنسل شود',
                        cancelButtonText: 'خیر',
                        cancelButtonColor: '#1e90ff',
                        confirmButtonColor: '#d33', // You can change the color to your preference
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.cancelAppontment();
                        }
                    });
                } else {
                    @this.authNeeded();
                }
            });
            $('body').on('click', '#termAndConditionAggrement', function() {
                if ($(this).is(':checked')) {
                    $('.payment-action-btn').prop('disabled', false);
                    $('#paymentBtn').removeClass('bg-gray-300');
                    $('#paymentBtn').addClass('bg-primary-main');
                    $('#paymentBtn').addClass('border-primary-main');
                } else {
                    $('.payment-action-btn').prop('disabled', true);
                    $('#paymentBtn').removeClass('bg-primary-main');
                    $('#paymentBtn').removeClass('border-primary-main');
                    $('#paymentBtn').addClass('bg-gray-300');
                }
            });
        });
    </script>
@endpush
