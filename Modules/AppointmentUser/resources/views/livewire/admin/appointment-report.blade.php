@php
    $fa = fn ($n) => strtr(number_format((float) $n), ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    $faNum = fn ($s) => strtr((string) $s, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', '.' => '٫']);
    $pct = fn ($part, $whole) => $whole > 0 ? round($part * 100 / $whole, 1) : 0;
    $kpi = $report['kpi'];
    $presets = ['7d' => '۷ روز', '30d' => '۳۰ روز', '90d' => '۳ ماه', '6m' => '۶ ماه', 'year' => 'امسال', '1y' => '۱ سال', 'all' => 'همه'];
    $doctorName = $doctorId !== '' ? ($doctors[$doctorId] ?? null) : null;
    $registrarName = match (true) {
        $registrar === 'patients' => 'خود مراجعین',
        $registrar === 'staff' => 'همه منشی‌ها و مدیران',
        $registrar !== '' => $registrars[$registrar] ?? null,
        default => null,
    };
    $hasFilters = $doctorId !== '' || $registrar !== '' || $dateBasis !== 'created' || $preset !== '90d';
@endphp

<div class="ar-page" dir="rtl">
    <style>
        .ar-page{--ar-ink:#101828;--ar-muted:#667085;--ar-line:#eaecf0;--ar-soft:#f8fafc;--ar-brand:#0f766e;--ar-brand-soft:#e6f4f1;--ar-self:#12b76a;--ar-staff:#2e90fa;--ar-voip:#f79009;--ar-danger:#f04438;--ar-pay:#7a5af8;color:var(--ar-ink);padding-bottom:28px}
        .ar-page *{box-sizing:border-box}
        .ar-hero{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:14px;margin:18px 0 16px}
        .ar-hero h1{font-size:22px;font-weight:800;margin:0 0 6px;display:flex;align-items:center;gap:10px}
        .ar-hero h1 .ar-hero-icon{width:40px;height:40px;border-radius:12px;display:inline-grid;place-items:center;background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;font-size:17px;box-shadow:0 6px 16px rgba(15,118,110,.25)}
        .ar-hero p{margin:0;color:var(--ar-muted);font-size:13px}
        .ar-chip-row{display:flex;flex-wrap:wrap;gap:6px}
        .ar-chip{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;padding:5px 10px;border-radius:999px;background:#fff;border:1px solid var(--ar-line);color:#344054}
        .ar-chip i{color:var(--ar-brand)}

        .ar-card{background:#fff;border:1px solid var(--ar-line);border-radius:16px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
        .ar-filters{padding:16px;margin-bottom:18px;position:relative;overflow:hidden}
        .ar-filters-top{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px}
        .ar-filters-title{font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px}
        .ar-seg{display:inline-flex;flex-wrap:wrap;gap:4px;padding:4px;background:var(--ar-soft);border:1px solid var(--ar-line);border-radius:12px}
        .ar-seg button{border:0;background:transparent;color:#475467;font-size:12.5px;font-weight:600;padding:6px 12px;border-radius:9px;cursor:pointer;transition:all .15s;white-space:nowrap}
        .ar-seg button:hover{background:#fff;color:var(--ar-ink)}
        .ar-seg button.is-active{background:#fff;color:var(--ar-brand);box-shadow:0 1px 3px rgba(16,24,40,.12)}
        .ar-filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;align-items:end}
        .ar-field label{display:block;font-size:12px;font-weight:600;color:#475467;margin-bottom:6px}
        .ar-input{display:flex;align-items:center;gap:8px;height:42px;padding:0 12px;border:1px solid #d0d5dd;border-radius:10px;background:#fff;transition:border-color .15s,box-shadow .15s}
        .ar-input:focus-within{border-color:var(--ar-brand);box-shadow:0 0 0 3px rgba(15,118,110,.12)}
        .ar-input i{color:#98a2b3;font-size:13px}
        .ar-input input,.ar-input select{border:0;outline:0;background:transparent;width:100%;height:100%;font-size:13px;color:var(--ar-ink);min-width:0}
        .ar-reset{height:42px;border-radius:10px;border:1px solid #fda29b;background:#fff;color:#b42318;font-size:13px;font-weight:600;padding:0 14px;cursor:pointer;white-space:nowrap}
        .ar-reset:hover{background:#fef3f2}
        .ar-progress{position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,transparent,var(--ar-brand),transparent);background-size:50% 100%;animation:ar-slide 1s linear infinite}
        @keyframes ar-slide{from{background-position:-50% 0}to{background-position:150% 0}}

        .ar-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
        .ar-kpi{padding:16px;position:relative;overflow:hidden}
        .ar-kpi::after{content:'';position:absolute;inset:auto -30px -40px auto;width:120px;height:120px;border-radius:50%;background:var(--kpi-soft,#f2f4f7);opacity:.7}
        .ar-kpi>*{position:relative;z-index:1}
        .ar-kpi-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
        .ar-kpi-label{font-size:12.5px;font-weight:600;color:var(--ar-muted)}
        .ar-kpi-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:15px;color:var(--kpi-color,#475467);background:var(--kpi-soft,#f2f4f7)}
        .ar-kpi-value{font-size:24px;font-weight:800;letter-spacing:-.3px;line-height:1.2}
        .ar-kpi-value small{font-size:12px;font-weight:600;color:var(--ar-muted);margin-right:4px}
        .ar-kpi-foot{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px;font-size:12px;color:var(--ar-muted)}
        .ar-delta{display:inline-flex;align-items:center;gap:4px;font-weight:700;font-size:11.5px;padding:2px 8px;border-radius:999px}
        .ar-delta.up{background:#ecfdf3;color:#067647}.ar-delta.down{background:#fef3f2;color:#b42318}.ar-delta.flat{background:#f2f4f7;color:#475467}
        .ar-bar{height:6px;border-radius:999px;background:#f2f4f7;overflow:hidden;margin-top:10px;display:flex}
        .ar-bar span{display:block;height:100%}

        .ar-section-title{display:flex;align-items:center;gap:10px;font-size:15px;font-weight:800;margin:26px 0 12px}
        .ar-section-title::after{content:'';flex:1;height:1px;background:var(--ar-line)}
        .ar-section-title i{color:var(--ar-brand)}
        .ar-grid{display:grid;gap:14px}
        .ar-grid-3-1{grid-template-columns:minmax(0,2.2fr) minmax(0,1fr)}
        .ar-grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
        .ar-grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}
        .ar-panel{padding:16px;min-width:0}
        .ar-panel-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}
        .ar-panel-title{font-size:14px;font-weight:700;margin:0}
        .ar-panel-sub{font-size:12px;color:var(--ar-muted);margin-top:3px}
        .ar-chart{position:relative;height:300px}
        .ar-chart.tall{height:380px}.ar-chart.short{height:250px}
        .ar-chart .ar-empty{display:none;position:absolute;inset:0;align-items:center;justify-content:center;flex-direction:column;gap:6px;color:#98a2b3;font-size:13px;background:#fff}
        .ar-chart.is-empty .ar-empty{display:flex}
        .ar-chart .ar-empty i{font-size:22px}

        .ar-table-wrap{overflow-x:auto}
        .ar-table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px;min-width:760px}
        .ar-table th{background:var(--ar-soft);color:var(--ar-muted);font-size:12px;font-weight:700;padding:10px 12px;text-align:right;border-bottom:1px solid var(--ar-line);white-space:nowrap}
        .ar-table td{padding:10px 12px;border-bottom:1px solid #f2f4f7;white-space:nowrap}
        .ar-table tbody tr:hover td{background:#fcfcfd}
        .ar-table tfoot td{font-weight:800;background:var(--ar-soft);border-top:1px solid var(--ar-line)}
        .ar-num{font-variant-numeric:tabular-nums}
        .ar-name{display:flex;align-items:center;gap:10px;font-weight:600}
        .ar-avatar{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:var(--ar-brand-soft);color:var(--ar-brand);font-size:12px;font-weight:800;flex:none}
        .ar-share{display:flex;align-items:center;gap:8px;min-width:120px}
        .ar-share .ar-bar{flex:1;margin:0}
        .ar-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-left:6px}
        .ar-note{font-size:12px;color:var(--ar-muted);display:flex;align-items:center;gap:6px}

        @media (max-width:1200px){.ar-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.ar-grid-3-1,.ar-grid-3{grid-template-columns:minmax(0,1fr)}}
        @media (max-width:768px){.ar-kpis,.ar-grid-2{grid-template-columns:minmax(0,1fr)}.ar-hero h1{font-size:19px}}
    </style>

    <div class="ar-hero">
        <div>
            <h1><span class="ar-hero-icon"><i class="fa fa-pie-chart"></i></span> گزارش عمومی نوبت‌دهی</h1>
            <p>
                از {{ $faNum(verta($from)->format('Y/m/d')) }} تا {{ $faNum(verta($to)->format('Y/m/d')) }}
                · بر اساس {{ $dateBasis === 'visit' ? 'تاریخ نوبت' : 'تاریخ ثبت نوبت' }}
            </p>
        </div>
        <div class="ar-chip-row">
            <span class="ar-chip"><i class="fa fa-user-md"></i> {{ $doctorName ?? 'همه پزشکان' }}</span>
            <span class="ar-chip"><i class="fa fa-user"></i> {{ $registrarName ?? 'همه ثبت‌کنندگان' }}</span>
        </div>
    </div>

    {{-- فیلترها --}}
    <div class="ar-card ar-filters">
        <div class="ar-progress" wire:loading></div>
        <div class="ar-filters-top">
            <span class="ar-filters-title"><i class="fa fa-sliders"></i> محدود کردن گزارش</span>
            <div class="ar-seg" role="group" aria-label="بازه زمانی">
                @foreach ($presets as $key => $label)
                    <button type="button" wire:click="applyPreset('{{ $key }}')" class="{{ $preset === $key ? 'is-active' : '' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="ar-filter-grid">
            <div class="ar-field">
                <label>از تاریخ</label>
                <div class="ar-input"><i class="fa fa-calendar"></i><input data-jdp wire:model.live="dateFrom" type="text" placeholder="از تاریخ" autocomplete="off" readonly></div>
            </div>
            <div class="ar-field">
                <label>تا تاریخ</label>
                <div class="ar-input"><i class="fa fa-calendar"></i><input data-jdp wire:model.live="dateTo" type="text" placeholder="تا تاریخ" autocomplete="off" readonly></div>
            </div>
            <div class="ar-field">
                <label>پزشک</label>
                <div class="ar-input"><i class="fa fa-user-md"></i>
                    <select wire:model.live="doctorId">
                        <option value="">همه پزشکان</option>
                        @foreach ($doctors as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ar-field">
                <label>ثبت‌کننده نوبت</label>
                <div class="ar-input"><i class="fa fa-user-plus"></i>
                    <select wire:model.live="registrar">
                        <option value="">همه</option>
                        <option value="patients">خود مراجعین (سایت و ویپ)</option>
                        <option value="staff">همه منشی‌ها و مدیران</option>
                        @if ($registrars->isNotEmpty())
                            <optgroup label="منشی‌ها و مدیران">
                                @foreach ($registrars as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
            </div>
            <div class="ar-field">
                <label>مبنای تاریخ نوبت‌ها</label>
                <div class="ar-seg" style="display:flex">
                    <button type="button" style="flex:1" wire:click="setDateBasis('created')" class="{{ $dateBasis === 'created' ? 'is-active' : '' }}">تاریخ ثبت</button>
                    <button type="button" style="flex:1" wire:click="setDateBasis('visit')" class="{{ $dateBasis === 'visit' ? 'is-active' : '' }}">تاریخ نوبت</button>
                </div>
            </div>
            @if ($hasFilters)
                <div class="ar-field">
                    <button type="button" class="ar-reset" wire:click="resetFilters"><i class="fa fa-times"></i> پاک کردن فیلترها</button>
                </div>
            @endif
        </div>
    </div>

    {{-- شاخص ها --}}
    @php
        $patientsTotal = $kpi['self'] + $kpi['voip'];
        $delta = function ($value) use ($faNum) {
            if ($value === null) {
                return '<span class="ar-delta flat">بدون داده در دوره قبل</span>';
            }
            $class = $value > 0 ? 'up' : ($value < 0 ? 'down' : 'flat');
            $icon = $value > 0 ? 'fa-arrow-up' : ($value < 0 ? 'fa-arrow-down' : 'fa-minus');

            return '<span class="ar-delta '.$class.'"><i class="fa '.$icon.'"></i> '.$faNum(abs($value)).'٪</span>';
        };
    @endphp
    <div class="ar-kpis">
        <div class="ar-card ar-kpi" style="--kpi-color:#0f766e;--kpi-soft:#e6f4f1">
            <div class="ar-kpi-head"><span class="ar-kpi-label">کل نوبت‌ها</span><span class="ar-kpi-icon"><i class="fa fa-calendar-check-o"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($kpi['total']) }}</div>
            <div class="ar-kpi-foot">{!! $delta($kpi['total_change']) !!} <span title="{{ $kpi['previous_label'] }}">نسبت به دوره قبل</span></div>
            <div class="ar-bar" title="سایت / منشی / ویپ">
                <span style="width:{{ $pct($kpi['self'], $kpi['total']) }}%;background:var(--ar-self)"></span>
                <span style="width:{{ $pct($kpi['staff'], $kpi['total']) }}%;background:var(--ar-staff)"></span>
                <span style="width:{{ $pct($kpi['voip'], $kpi['total']) }}%;background:var(--ar-voip)"></span>
            </div>
        </div>
        <div class="ar-card ar-kpi" style="--kpi-color:#067647;--kpi-soft:#ecfdf3">
            <div class="ar-kpi-head"><span class="ar-kpi-label">دریافتی توسط مراجعین</span><span class="ar-kpi-icon"><i class="fa fa-globe"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($patientsTotal) }} <small>{{ $faNum($pct($patientsTotal, $kpi['total'])) }}٪</small></div>
            <div class="ar-kpi-foot"><span><span class="ar-dot" style="background:var(--ar-self)"></span>سایت {{ $fa($kpi['self']) }}</span><span><span class="ar-dot" style="background:var(--ar-voip)"></span>ویپ {{ $fa($kpi['voip']) }}</span></div>
        </div>
        <div class="ar-card ar-kpi" style="--kpi-color:#175cd3;--kpi-soft:#eff8ff">
            <div class="ar-kpi-head"><span class="ar-kpi-label">ثبت‌شده توسط منشی / مدیریت</span><span class="ar-kpi-icon"><i class="fa fa-user-plus"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($kpi['staff']) }} <small>{{ $faNum($pct($kpi['staff'], $kpi['total'])) }}٪</small></div>
            <div class="ar-kpi-foot">{{ $fa($report['registrars']->count()) }} ثبت‌کننده فعال</div>
        </div>
        <div class="ar-card ar-kpi" style="--kpi-color:#6941c6;--kpi-soft:#f4f3ff">
            <div class="ar-kpi-head"><span class="ar-kpi-label">مجموع پرداختی‌ها</span><span class="ar-kpi-icon"><i class="fa fa-money"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($kpi['pay_sum']) }} <small>ریال</small></div>
            <div class="ar-kpi-foot">{!! $delta($kpi['pay_change']) !!} <span title="{{ $kpi['previous_label'] }}">نسبت به دوره قبل</span></div>
        </div>
        <div class="ar-card ar-kpi" style="--kpi-color:#c11574;--kpi-soft:#fdf2fa">
            <div class="ar-kpi-head"><span class="ar-kpi-label">تعداد پرداخت موفق</span><span class="ar-kpi-icon"><i class="fa fa-credit-card"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($kpi['pay_count']) }}</div>
            <div class="ar-kpi-foot">میانگین هر پرداخت {{ $fa($kpi['pay_avg']) }} ریال</div>
        </div>
        <div class="ar-card ar-kpi" style="--kpi-color:#b54708;--kpi-soft:#fffaeb">
            <div class="ar-kpi-head"><span class="ar-kpi-label">مراجعین یکتا</span><span class="ar-kpi-icon"><i class="fa fa-users"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($kpi['patients']) }}</div>
            <div class="ar-kpi-foot">به طور میانگین {{ $faNum($kpi['patients'] ? round($kpi['total'] / $kpi['patients'], 1) : 0) }} نوبت برای هر مراجع</div>
        </div>
        <div class="ar-card ar-kpi" style="--kpi-color:#026aa2;--kpi-soft:#f0f9ff">
            <div class="ar-kpi-head"><span class="ar-kpi-label">پزشکان دارای نوبت</span><span class="ar-kpi-icon"><i class="fa fa-user-md"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($kpi['doctors']) }}</div>
            <div class="ar-kpi-foot">حضور یافته: {{ $fa($kpi['attended']) }} نوبت</div>
        </div>
        <div class="ar-card ar-kpi" style="--kpi-color:#b42318;--kpi-soft:#fef3f2">
            <div class="ar-kpi-head"><span class="ar-kpi-label">نوبت‌های لغو / رد شده</span><span class="ar-kpi-icon"><i class="fa fa-ban"></i></span></div>
            <div class="ar-kpi-value ar-num">{{ $fa($kpi['canceled']) }} <small>{{ $faNum($kpi['cancel_rate']) }}٪</small></div>
            <div class="ar-kpi-foot">نرخ لغو از کل نوبت‌های بازه</div>
        </div>
    </div>

    {{-- روند نوبت ها --}}
    <div class="ar-section-title"><i class="fa fa-line-chart"></i> روند نوبت‌ها</div>
    <div class="ar-grid ar-grid-3-1">
        <div class="ar-card ar-panel">
            <div class="ar-panel-head">
                <div>
                    <h3 class="ar-panel-title">تعداد نوبت‌ها به تفکیک منبع ثبت</h3>
                    <div class="ar-panel-sub">ستون‌ها: سایت، منشی و ویپ · خط: نوبت‌های لغو شده</div>
                </div>
                <div class="ar-seg">
                    <button type="button" wire:click="setGranularity('day')" class="{{ $granularity === 'day' ? 'is-active' : '' }}">روزانه</button>
                    <button type="button" wire:click="setGranularity('week')" class="{{ $granularity === 'week' ? 'is-active' : '' }}">هفتگی</button>
                    <button type="button" wire:click="setGranularity('month')" class="{{ $granularity === 'month' ? 'is-active' : '' }}">ماهانه</button>
                </div>
            </div>
            <div class="ar-chart tall" wire:ignore><canvas id="ar-trend"></canvas><div class="ar-empty"><i class="fa fa-bar-chart"></i>داده‌ای برای نمایش نیست</div></div>
        </div>
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">سهم منبع ثبت نوبت</h3><div class="ar-panel-sub">مراجع از سایت، منشی/مدیریت یا ویپ</div></div></div>
            <div class="ar-chart tall" wire:ignore><canvas id="ar-sources"></canvas><div class="ar-empty"><i class="fa fa-pie-chart"></i>داده‌ای برای نمایش نیست</div></div>
        </div>
    </div>

    {{-- پزشکان --}}
    <div class="ar-section-title"><i class="fa fa-user-md"></i> پزشکان</div>
    <div class="ar-grid ar-grid-2">
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">تعداد نوبت‌های پزشکان</h3><div class="ar-panel-sub">۱۵ پزشک پرنوبت به تفکیک منبع ثبت</div></div></div>
            <div class="ar-chart tall" wire:ignore><canvas id="ar-doctors"></canvas><div class="ar-empty"><i class="fa fa-bar-chart"></i>داده‌ای برای نمایش نیست</div></div>
        </div>
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">پرداخت‌ها به تفکیک پزشک</h3><div class="ar-panel-sub">مجموع پرداخت موفق (ریال)</div></div></div>
            <div class="ar-chart tall" wire:ignore><canvas id="ar-doctor-payments"></canvas><div class="ar-empty"><i class="fa fa-bar-chart"></i>پرداختی در این بازه ثبت نشده</div></div>
        </div>
    </div>

    {{-- ثبت کنندگان و توزیع ها --}}
    <div class="ar-section-title"><i class="fa fa-users"></i> ثبت‌کنندگان و توزیع نوبت‌ها</div>
    <div class="ar-grid ar-grid-3">
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">نوبت‌های ثبت‌شده توسط منشی‌ها</h3><div class="ar-panel-sub">به تفکیک ثبت‌کننده</div></div></div>
            <div class="ar-chart" wire:ignore><canvas id="ar-registrars"></canvas><div class="ar-empty"><i class="fa fa-bar-chart"></i>نوبتی توسط منشی ثبت نشده</div></div>
        </div>
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">وضعیت نوبت‌ها</h3><div class="ar-panel-sub">توزیع وضعیت فعلی نوبت‌ها</div></div></div>
            <div class="ar-chart" wire:ignore><canvas id="ar-status"></canvas><div class="ar-empty"><i class="fa fa-pie-chart"></i>داده‌ای برای نمایش نیست</div></div>
        </div>
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">روزهای هفته</h3><div class="ar-panel-sub">پرترددترین روزها</div></div></div>
            <div class="ar-chart" wire:ignore><canvas id="ar-weekday"></canvas><div class="ar-empty"><i class="fa fa-bar-chart"></i>داده‌ای برای نمایش نیست</div></div>
        </div>
    </div>

    {{-- پرداخت ها --}}
    <div class="ar-section-title"><i class="fa fa-money"></i> پرداخت‌ها</div>
    <div class="ar-grid ar-grid-3-1">
        <div class="ar-card ar-panel">
            <div class="ar-panel-head">
                <div>
                    <h3 class="ar-panel-title">پرداخت‌ها به تفکیک روز</h3>
                    <div class="ar-panel-sub">ستون: مبلغ روزانه · خط: مجموع تجمعی</div>
                </div>
                @if ($report['daily_truncated'])
                    <span class="ar-note"><i class="fa fa-info-circle"></i> ۱۸۰ روز آخر بازه نمایش داده می‌شود</span>
                @endif
            </div>
            <div class="ar-chart tall" wire:ignore><canvas id="ar-pay-daily"></canvas><div class="ar-empty"><i class="fa fa-bar-chart"></i>پرداختی در این بازه ثبت نشده</div></div>
        </div>
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">روش پرداخت</h3><div class="ar-panel-sub">سهم هر روش از مبلغ دریافتی</div></div></div>
            <div class="ar-chart tall" wire:ignore><canvas id="ar-paid-by"></canvas><div class="ar-empty"><i class="fa fa-pie-chart"></i>پرداختی در این بازه ثبت نشده</div></div>
        </div>
    </div>
    <div class="ar-grid" style="margin-top:14px">
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">پرداخت‌ها به تفکیک ماه</h3><div class="ar-panel-sub">ستون: مبلغ هر ماه · خط: مجموع تجمعی پرداختی‌ها</div></div></div>
            <div class="ar-chart" wire:ignore><canvas id="ar-pay-monthly"></canvas><div class="ar-empty"><i class="fa fa-bar-chart"></i>پرداختی در این بازه ثبت نشده</div></div>
        </div>
    </div>

    {{-- جدول پزشکان --}}
    <div class="ar-section-title"><i class="fa fa-table"></i> جزئیات</div>
    <div class="ar-card ar-panel">
        <div class="ar-panel-head"><div><h3 class="ar-panel-title">عملکرد پزشکان</h3><div class="ar-panel-sub">نوبت‌ها بر اساس {{ $dateBasis === 'visit' ? 'تاریخ نوبت' : 'تاریخ ثبت' }} و پرداخت‌ها بر اساس تاریخ پرداخت</div></div></div>
        <div class="ar-table-wrap">
            <table class="ar-table">
                <thead>
                    <tr>
                        <th>پزشک</th><th>کل نوبت‌ها</th><th>سهم از کل</th><th>سایت</th><th>منشی / مدیریت</th><th>ویپ</th><th>لغو شده</th><th>حضور یافته</th><th>تعداد پرداخت</th><th>مبلغ پرداختی (ریال)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['doctors'] as $row)
                        <tr wire:key="ar-doctor-{{ $row['id'] }}">
                            <td><span class="ar-name"><span class="ar-avatar">{{ mb_substr($row['name'], 0, 1) }}</span>{{ $row['name'] }}</span></td>
                            <td class="ar-num"><strong>{{ $fa($row['total']) }}</strong></td>
                            <td><span class="ar-share"><span class="ar-bar"><span style="width:{{ $pct($row['total'], $kpi['total']) }}%;background:var(--ar-brand)"></span></span><span class="ar-num">{{ $faNum($pct($row['total'], $kpi['total'])) }}٪</span></span></td>
                            <td class="ar-num">{{ $fa($row['self']) }}</td>
                            <td class="ar-num">{{ $fa($row['staff']) }}</td>
                            <td class="ar-num">{{ $fa($row['voip']) }}</td>
                            <td class="ar-num" style="color:#b42318">{{ $fa($row['canceled']) }}</td>
                            <td class="ar-num">{{ $fa($row['attended']) }}</td>
                            <td class="ar-num">{{ $fa($row['pay_count']) }}</td>
                            <td class="ar-num"><strong>{{ $fa($row['pay_sum']) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" style="text-align:center;color:#98a2b3;padding:28px">داده‌ای برای نمایش نیست</td></tr>
                    @endforelse
                </tbody>
                @if ($report['doctors']->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td>جمع</td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('total')) }}</td>
                            <td></td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('self')) }}</td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('staff')) }}</td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('voip')) }}</td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('canceled')) }}</td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('attended')) }}</td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('pay_count')) }}</td>
                            <td class="ar-num">{{ $fa($report['doctors']->sum('pay_sum')) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="ar-grid ar-grid-2" style="margin-top:14px">
        {{-- جدول ثبت کنندگان --}}
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">ثبت‌کنندگان نوبت</h3><div class="ar-panel-sub">منشی‌ها و مدیرانی که برای مراجع نوبت ثبت کرده‌اند</div></div></div>
            <div class="ar-table-wrap">
                <table class="ar-table" style="min-width:520px">
                    <thead><tr><th>ثبت‌کننده</th><th>تعداد نوبت</th><th>سهم</th><th>پزشکان</th><th>لغو شده</th><th>پرداختی (ریال)</th></tr></thead>
                    <tbody>
                        @foreach ($report['registrars'] as $row)
                            <tr wire:key="ar-registrar-{{ $row['id'] }}">
                                <td><span class="ar-name"><span class="ar-avatar" style="background:#eff8ff;color:#175cd3">{{ mb_substr($row['name'], 0, 1) }}</span>{{ $row['name'] }}</span></td>
                                <td class="ar-num"><strong>{{ $fa($row['total']) }}</strong></td>
                                <td class="ar-num">{{ $faNum($pct($row['total'], $kpi['total'])) }}٪</td>
                                <td class="ar-num">{{ $fa($row['doctors']) }}</td>
                                <td class="ar-num" style="color:#b42318">{{ $fa($row['canceled']) }}</td>
                                <td class="ar-num">{{ $fa($row['pay_sum']) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td><span class="ar-name"><span class="ar-avatar" style="background:#ecfdf3;color:#067647"><i class="fa fa-globe"></i></span>خود مراجعین (سایت)</span></td>
                            <td class="ar-num"><strong>{{ $fa($kpi['self']) }}</strong></td>
                            <td class="ar-num">{{ $faNum($pct($kpi['self'], $kpi['total'])) }}٪</td>
                            <td colspan="3" class="ar-muted"></td>
                        </tr>
                        <tr>
                            <td><span class="ar-name"><span class="ar-avatar" style="background:#fffaeb;color:#b54708"><i class="fa fa-phone"></i></span>خود مراجعین (ویپ)</span></td>
                            <td class="ar-num"><strong>{{ $fa($kpi['voip']) }}</strong></td>
                            <td class="ar-num">{{ $faNum($pct($kpi['voip'], $kpi['total'])) }}٪</td>
                            <td colspan="3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- روش پرداخت --}}
        <div class="ar-card ar-panel">
            <div class="ar-panel-head"><div><h3 class="ar-panel-title">پرداخت‌ها به تفکیک روش</h3><div class="ar-panel-sub">فقط پرداخت‌های موفق با مبلغ بیشتر از صفر</div></div></div>
            <div class="ar-table-wrap">
                <table class="ar-table" style="min-width:420px">
                    <thead><tr><th>روش پرداخت</th><th>تعداد</th><th>مبلغ (ریال)</th><th>سهم</th></tr></thead>
                    <tbody>
                        @forelse ($report['paid_by'] as $row)
                            <tr>
                                <td><strong>{{ $row['label'] }}</strong></td>
                                <td class="ar-num">{{ $fa($row['count']) }}</td>
                                <td class="ar-num">{{ $fa($row['sum']) }}</td>
                                <td><span class="ar-share"><span class="ar-bar"><span style="width:{{ $pct($row['sum'], $kpi['pay_sum']) }}%;background:var(--ar-pay)"></span></span><span class="ar-num">{{ $faNum($pct($row['sum'], $kpi['pay_sum'])) }}٪</span></span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:#98a2b3;padding:28px">پرداختی در این بازه ثبت نشده</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- جدول ماهانه --}}
    <div class="ar-card ar-panel" style="margin-top:14px">
        <div class="ar-panel-head"><div><h3 class="ar-panel-title">خلاصه ماهانه</h3><div class="ar-panel-sub">نوبت‌ها و پرداخت‌های هر ماه شمسی به همراه مجموع تجمعی</div></div></div>
        <div class="ar-table-wrap">
            <table class="ar-table">
                <thead><tr><th>ماه</th><th>کل نوبت‌ها</th><th>سایت</th><th>منشی / مدیریت</th><th>ویپ</th><th>تعداد پرداخت</th><th>مبلغ پرداختی (ریال)</th><th>مجموع تجمعی (ریال)</th></tr></thead>
                <tbody>
                    @foreach (array_reverse($report['monthly']) as $row)
                        <tr>
                            <td><strong>{{ $faNum($row['label']) }}</strong></td>
                            <td class="ar-num"><strong>{{ $fa($row['total']) }}</strong></td>
                            <td class="ar-num">{{ $fa($row['self']) }}</td>
                            <td class="ar-num">{{ $fa($row['staff']) }}</td>
                            <td class="ar-num">{{ $fa($row['voip']) }}</td>
                            <td class="ar-num">{{ $fa($row['pay_count']) }}</td>
                            <td class="ar-num">{{ $fa($row['pay_sum']) }}</td>
                            <td class="ar-num" style="color:#6941c6"><strong>{{ $fa($row['cumulative']) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script type="application/json" id="ar-report-data">@json($report['charts'])</script>
</div>

@assets
<script src="{{ asset('assets/admin/plugins/chart/Chart.bundle.js') }}"></script>
@endassets

@script
<script>
    const charts = {};
    let lastPayload = '';
    const colors = { self: '#12b76a', staff: '#2e90fa', voip: '#f79009', danger: '#f04438', pay: '#7a5af8', brand: '#0f766e' };
    const palette = ['#0f766e', '#2e90fa', '#7a5af8', '#f79009', '#12b76a', '#ee46bc', '#0ba5ec', '#f04438', '#667085', '#fdb022'];
    const toFa = (n) => Number(n || 0).toLocaleString('fa-IR');
    const compact = (n) => {
        n = Number(n || 0);
        const abs = Math.abs(n);
        if (abs >= 1e9) return toFa(+(n / 1e9).toFixed(1)) + ' میلیارد';
        if (abs >= 1e6) return toFa(+(n / 1e6).toFixed(1)) + ' میلیون';
        if (abs >= 1e3) return toFa(+(n / 1e3).toFixed(1)) + ' هزار';
        return toFa(n);
    };
    const hasData = (values) => (values || []).some((v) => Number(v) > 0);

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#667085';
    Chart.defaults.borderColor = '#eef0f3';
    Chart.defaults.plugins.legend.rtl = true;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.padding = 14;
    Chart.defaults.plugins.tooltip.rtl = true;
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 10;
    Chart.defaults.plugins.tooltip.backgroundColor = '#101828';
    Chart.defaults.maintainAspectRatio = false;

    function draw(id, has, config) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        canvas.parentElement.classList.toggle('is-empty', !has);
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(canvas, config);
    }

    const countTooltip = (axis) => ({ callbacks: { label: (ctx) => ` ${ctx.dataset.label || ctx.label}: ${toFa(axis ? ctx.parsed[axis] : ctx.parsed)}` } });
    const moneyTooltip = (axis) => ({ callbacks: { label: (ctx) => ` ${ctx.dataset.label || ctx.label}: ${toFa(axis ? ctx.parsed[axis] : ctx.parsed)} ریال` } });
    const doughnut = (labels, data, bg, tooltip) => ({
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: bg, borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }] },
        options: { cutout: '68%', plugins: { legend: { position: 'bottom' }, tooltip } },
    });
    const bar = (opts) => ({ borderRadius: 6, borderSkipped: false, maxBarThickness: 34, ...opts });

    function render(r) {
        const payload = JSON.stringify(r);
        if (payload === lastPayload) return;
        lastPayload = payload;

        const t = r.trend;
        draw('ar-trend', hasData([...t.self, ...t.staff, ...t.voip]), {
            data: {
                labels: t.labels,
                datasets: [
                    { type: 'line', label: 'لغو شده', data: t.canceled, borderColor: colors.danger, backgroundColor: colors.danger, borderWidth: 2, pointRadius: 2, tension: .35, order: 0 },
                    { type: 'bar', label: 'سایت (خود مراجع)', data: t.self, backgroundColor: colors.self, stack: 's', ...bar({ borderRadius: 4 }) },
                    { type: 'bar', label: 'منشی / مدیریت', data: t.staff, backgroundColor: colors.staff, stack: 's', ...bar({ borderRadius: 4 }) },
                    { type: 'bar', label: 'ویپ', data: t.voip, backgroundColor: colors.voip, stack: 's', ...bar({ borderRadius: 4 }) },
                ],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true, ticks: { callback: (v) => toFa(v), precision: 0 } } },
                plugins: { legend: { position: 'top', align: 'start' }, tooltip: { ...countTooltip('y'), footer: (items) => 'مجموع: ' + toFa(items.filter((i) => i.dataset.type === 'bar').reduce((s, i) => s + i.parsed.y, 0)) } },
            },
        });

        draw('ar-sources', hasData(r.sources.counts), doughnut(r.sources.labels, r.sources.counts, [colors.self, colors.staff, colors.voip, '#98a2b3'], countTooltip()));

        draw('ar-doctors', hasData([...r.doctors.self, ...r.doctors.staff, ...r.doctors.voip]), {
            type: 'bar',
            data: {
                labels: r.doctors.labels,
                datasets: [
                    { label: 'سایت', data: r.doctors.self, backgroundColor: colors.self, ...bar({ borderRadius: 4 }) },
                    { label: 'منشی / مدیریت', data: r.doctors.staff, backgroundColor: colors.staff, ...bar({ borderRadius: 4 }) },
                    { label: 'ویپ', data: r.doctors.voip, backgroundColor: colors.voip, ...bar({ borderRadius: 4 }) },
                ],
            },
            options: {
                indexAxis: 'y',
                interaction: { mode: 'index', intersect: false },
                scales: { x: { stacked: true, beginAtZero: true, ticks: { callback: (v) => toFa(v), precision: 0 } }, y: { stacked: true, grid: { display: false } } },
                plugins: { legend: { position: 'top', align: 'start' }, tooltip: countTooltip('x') },
            },
        });

        draw('ar-doctor-payments', hasData(r.doctorPayments.sums), {
            type: 'bar',
            data: { labels: r.doctorPayments.labels, datasets: [{ label: 'مبلغ پرداختی', data: r.doctorPayments.sums, backgroundColor: colors.pay, ...bar() }] },
            options: {
                indexAxis: 'y',
                scales: { x: { beginAtZero: true, ticks: { callback: (v) => compact(v) } }, y: { grid: { display: false } } },
                plugins: { legend: { display: false }, tooltip: moneyTooltip('x') },
            },
        });

        draw('ar-registrars', hasData(r.registrars.counts), {
            type: 'bar',
            data: { labels: r.registrars.labels, datasets: [{ label: 'نوبت ثبت‌شده', data: r.registrars.counts, backgroundColor: colors.staff, ...bar() }] },
            options: {
                indexAxis: 'y',
                scales: { x: { beginAtZero: true, ticks: { callback: (v) => toFa(v), precision: 0 } }, y: { grid: { display: false } } },
                plugins: { legend: { display: false }, tooltip: countTooltip('x') },
            },
        });

        draw('ar-status', hasData(r.status.counts), doughnut(r.status.labels, r.status.counts, r.status.colors, countTooltip()));

        draw('ar-weekday', hasData(r.weekday.counts), {
            type: 'bar',
            data: { labels: r.weekday.labels, datasets: [{ label: 'نوبت', data: r.weekday.counts, backgroundColor: r.weekday.counts.map((v) => v === Math.max(...r.weekday.counts) ? colors.brand : '#99d5cc'), ...bar() }] },
            options: {
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { callback: (v) => toFa(v), precision: 0 } } },
                plugins: { legend: { display: false }, tooltip: countTooltip('y') },
            },
        });

        const payCombo = (labels, sums, cumulative, sumLabel) => ({
            data: {
                labels,
                datasets: [
                    { type: 'line', label: 'مجموع تجمعی', data: cumulative, yAxisID: 'y1', borderColor: colors.brand, backgroundColor: 'rgba(15,118,110,.08)', fill: true, borderWidth: 2, pointRadius: 0, tension: .3, order: 0 },
                    { type: 'bar', label: sumLabel, data: sums, yAxisID: 'y', backgroundColor: colors.pay, ...bar({ borderRadius: 4 }) },
                ],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, position: 'right', title: { display: true, text: sumLabel }, ticks: { callback: (v) => compact(v) } },
                    y1: { beginAtZero: true, position: 'left', grid: { drawOnChartArea: false }, title: { display: true, text: 'تجمعی' }, ticks: { callback: (v) => compact(v) } },
                },
                plugins: { legend: { position: 'top', align: 'start' }, tooltip: moneyTooltip('y') },
            },
        });

        draw('ar-pay-daily', hasData(r.payDaily.sums), payCombo(r.payDaily.labels, r.payDaily.sums, r.payDaily.cumulative, 'مبلغ روزانه'));
        draw('ar-pay-monthly', hasData(r.payMonthly.sums), payCombo(r.payMonthly.labels, r.payMonthly.sums, r.payMonthly.cumulative, 'مبلغ ماهانه'));
        draw('ar-paid-by', hasData(r.paidBy.sums), doughnut(r.paidBy.labels, r.paidBy.sums, palette.slice(2), moneyTooltip()));
    }

    render(JSON.parse(document.getElementById('ar-report-data').textContent));
    $wire.on('appointment-report-updated', (event) => render(event.report));

    // تقویم شمسی؛ با انتخاب تاریخ رویداد input ارسال میشود و wire:model.live آن را میگیرد
    (function initPicker() {
        if (!window.jalaliDatepicker) { return setTimeout(initPicker, 100); }
        jalaliDatepicker.startWatch({ zIndex: 99999, autoHide: true, hideAfterChange: true });
    })();
</script>
@endscript
